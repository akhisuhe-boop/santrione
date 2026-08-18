<?php

namespace App\Console\Commands;

use App\Models\Yayasan;
use App\Services\XenditSubscriptionService;
use App\Services\TenantBillingCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * "Autopilot" billing TAHUNAN -- rekan dari GenerateTenantInvoices
 * (yang urus siklus bulanan). Dijadwalkan HARIAN (bukan tanggal tetap
 * seperti bulanan) lewat routes/console.php, karena tiap Yayasan
 * punya tanggal ulang tahun langganan yang beda-beda (tergantung
 * kapan mereka pertama bayar tahunan), tidak bisa disamakan ke 1
 * tanggal kalender seperti bulanan.
 *
 * Untuk tiap Yayasan yang subscription aktifnya siklus_billing=
 * 'tahunan' DAN berakhir_pada sudah masuk jendela H-7 (mau habis
 * dalam 7 hari) atau sudah lewat:
 *
 *  1. Kunci snapshot jumlah siswa aktif tiap Lembaga -- SAMA seperti
 *     billing bulanan, supaya konsisten.
 *  2. Hitung total tagihan TAHUN BERIKUTNYA lewat
 *     TenantBillingCalculator::hitungYayasanTahunan() -- SELALU
 *     dihitung ulang dari harga_bulanan/ModulePrice/diskon_tahunan_persen
 *     yang berlaku SEKARANG (bukan angka lama yang dibekukan), supaya
 *     kalau admin ubah harga modul tahun ini, tagihan perpanjangan
 *     tahun depan otomatis ikut harga baru.
 *  3. Lewati kalau invoice TAHUN itu SUDAH pernah dibuat (idempotent
 *     lewat kolom periode = tahun target, mis. "2027" -- aman
 *     dijalankan ulang tiap hari tanpa membuat tagihan dobel).
 *  4. Buat baris Subscription baru (status pending, siklus_billing=
 *     'tahunan', computed_amount + computed_breakdown terisi) lalu
 *     buat transaksi Xendit otomatis.
 *
 * Kegagalan pada satu Yayasan TIDAK menghentikan proses Yayasan lain
 * — dicatat ke log dan lanjut.
 */
class GenerateAnnualInvoices extends Command
{
    protected $signature = 'subscription:generate-annual-invoice {--dry-run : Hitung & tampilkan saja, jangan simpan/tagih}';

    protected $description = 'Cari pelanggan tahunan yang masa aktifnya mau habis (H-7), buat tagihan perpanjangan tahun berikutnya otomatis';

    public function handle(TenantBillingCalculator $calculator, XenditSubscriptionService $xendit): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $yayasans = Yayasan::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereHas('subscriptions', function ($q) {
                $q->where('status', 'active')
                    ->where('siklus_billing', 'tahunan')
                    ->whereBetween('berakhir_pada', [now()->subDay(), now()->addDays(7)])
                    ->whereHas('plan', fn ($p) => $p->whereNotNull('harga_per_siswa_tambahan'));
            })
            ->get();

        $this->info("Memproses {$yayasans->count()} yayasan (siklus tahunan, jendela perpanjangan)" . ($dryRun ? ' (dry-run)' : ''));

        foreach ($yayasans as $yayasan) {

            $subAktif = $yayasan->activeSubscription();

            if (! $subAktif) {
                continue;
            }

            // Label periode tahunan = tahun SAAT langganan yang baru
            // ini mulai berlaku (tahun berakhir_pada yang lama = tahun
            // mulai yang baru, karena berakhir_pada lama jadi
            // titik sambung). Contoh: berakhir_pada lama 2027-01-15
            // -> periode baru "2027".
            $periodeTarget = (string) $subAktif->berakhir_pada->year;

            // Idempotent: sudah ada invoice periode/tahun ini -> lewati.
            $sudahAda = $yayasan->subscriptions()
                ->where('periode', $periodeTarget)
                ->where('siklus_billing', 'tahunan')
                ->exists();

            if ($sudahAda) {
                $this->line("  - {$yayasan->nama}: sudah ada invoice tahunan periode {$periodeTarget}, dilewati.");

                continue;
            }

            foreach ($yayasan->lembagas as $lembaga) {
                if (! $dryRun) {
                    $lembaga->update([
                        'jumlah_siswa_billing' => $lembaga->jumlahSiswaAktif(),
                        'siswa_billing_snapshot_at' => now(),
                    ]);
                }
            }

            $hasil = $calculator->hitungYayasanTahunan($yayasan->fresh());

            $this->line("  - {$yayasan->nama}: Rp" . number_format($hasil['total'], 0, ',', '.') . " (tahun {$periodeTarget})");

            if ($dryRun) {
                continue;
            }

            $planAksesPlatform = $subAktif->plan;

            if (! $planAksesPlatform) {
                Log::warning("GenerateAnnualInvoices: yayasan {$yayasan->id} lolos filter tapi tidak punya plan, dilewati.");

                continue;
            }

            $subscriptionBaru = $yayasan->subscriptions()->create([
                'subscription_plan_id' => $planAksesPlatform->id,
                'siklus_billing' => 'tahunan',
                'status' => 'pending',
                'computed_amount' => $hasil['total'],
                'computed_breakdown' => $hasil,
                'periode' => $periodeTarget,
            ]);

            if (($hasil['promo_pendaftaran_persen'] ?? 0) > 0) {
                $yayasan->update(['promo_pendaftaran_terpakai' => true]);

                try {
                    \App\Services\NotificationService::sendBroadcastYayasan(
                        $yayasan,
                        'Diskon Pendaftaran Diterapkan',
                        "Tagihan pertama Anda sudah termasuk diskon pendaftaran \"{$hasil['promo_pendaftaran_teks']}\" ({$hasil['promo_pendaftaran_persen']}%). " .
                        "Diskon ini berlaku SATU KALI untuk tagihan pertama saja -- perpanjangan tahun berikutnya akan kembali ke harga normal."
                    );
                } catch (\Throwable $e) {
                    Log::error("GenerateAnnualInvoices: gagal kirim notif penjelasan promo untuk yayasan {$yayasan->id}: {$e->getMessage()}");
                }
            }

            try {
                $paymentUrl = $xendit->createTransaction(
                    $subscriptionBaru,
                    $planAksesPlatform,
                    $yayasan->email ?? 'billing@qinaraindonesia.id'
                );
            } catch (\Throwable $e) {
                Log::error("GenerateAnnualInvoices: gagal membuat transaksi Xendit untuk yayasan {$yayasan->id}: {$e->getMessage()}");
                $this->error("    Gagal membuat transaksi Xendit: {$e->getMessage()}");

                continue;
            }

            try {
                \App\Services\NotificationService::sendTagihanSubscriptionTahunan(
                    $yayasan,
                    $hasil['total'],
                    $paymentUrl,
                    $periodeTarget
                );
            } catch (\Throwable $e) {
                Log::error("GenerateAnnualInvoices: transaksi Xendit sukses tapi notifikasi WA gagal untuk yayasan {$yayasan->id}: {$e->getMessage()}");
            }
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
