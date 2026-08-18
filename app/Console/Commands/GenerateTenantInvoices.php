<?php

namespace App\Console\Commands;

use App\Models\Yayasan;
use App\Services\XenditSubscriptionService;
use App\Services\TenantBillingCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * "Autopilot" billing bulanan skema à la carte (lihat dokumen "Skema
 * Pembiayaan Qinara Apps"). Dijadwalkan tanggal 1 tiap bulan lewat
 * routes/console.php.
 *
 * Untuk tiap Yayasan yang pakai plan bergaya "Akses Platform" (ditandai
 * harga_per_siswa_tambahan terisi — lihat TenantBillingCalculator)
 * DAN siklus_billing subscription aktifnya 'bulanan' (pelanggan
 * TAHUNAN sengaja DIKECUALIKAN dari sini -- mereka ditagih lewat
 * command terpisah subscription:generate-annual-invoice, supaya tidak
 * dobel tagih dalam periode yang sama):
 *
 *  1. Kunci snapshot jumlah siswa aktif tiap Lembaga (jumlah_siswa_billing)
 *     — supaya siswa masuk/keluar di TENGAH bulan tidak mengubah
 *     tagihan yang sudah terbit.
 *  2. Hitung total tagihan (TenantBillingCalculator — SATU-SATUNYA
 *     sumber rumus, tidak ada perhitungan manual terpisah).
 *  3. Lewati kalau invoice periode ini SUDAH pernah dibuat (idempotent
 *     — command ini aman dijalankan ulang tanpa membuat tagihan dobel).
 *  4. Buat baris Subscription baru (status pending, computed_amount +
 *     computed_breakdown terisi) lalu buat transaksi Xendit otomatis.
 *
 * Kegagalan pada satu Yayasan (mis. Xendit error) TIDAK menghentikan
 * proses Yayasan lain — dicatat ke log dan lanjut.
 */
class GenerateTenantInvoices extends Command
{
    protected $signature = 'subscription:generate-monthly-invoice {--dry-run : Hitung & tampilkan saja, jangan simpan/tagih}';

    protected $description = 'Kunci snapshot siswa, hitung tagihan bulanan (Akses Platform + modul + diskon), lalu buat transaksi Xendit otomatis untuk tiap Yayasan (siklus bulanan saja)';

    public function handle(TenantBillingCalculator $calculator, XenditSubscriptionService $xendit): int
    {
        $periode = now()->format('Y-m');
        $dryRun = (bool) $this->option('dry-run');

        $yayasans = Yayasan::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereHas('subscriptions', function ($q) {
                $q->where('status', 'active')
                    ->where('siklus_billing', 'bulanan')
                    ->where('berakhir_pada', '>', now())
                    ->whereHas('plan', fn ($p) => $p->whereNotNull('harga_per_siswa_tambahan'));
            })
            ->get();

        $this->info("Memproses {$yayasans->count()} yayasan untuk periode {$periode}" . ($dryRun ? ' (dry-run)' : ''));

        foreach ($yayasans as $yayasan) {

            // Idempotent: sudah ada invoice periode ini -> lewati.
            $sudahAda = $yayasan->subscriptions()->where('periode', $periode)->exists();

            if ($sudahAda) {
                $this->line("  - {$yayasan->nama}: sudah ada invoice periode {$periode}, dilewati.");

                continue;
            }

            // Kunci snapshot jumlah siswa tiap Lembaga sebelum dihitung.
            foreach ($yayasan->lembagas as $lembaga) {
                if (! $dryRun) {
                    $lembaga->update([
                        'jumlah_siswa_billing' => $lembaga->jumlahSiswaAktif(),
                        'siswa_billing_snapshot_at' => now(),
                    ]);
                }
            }

            $hasil = $calculator->hitungYayasan($yayasan->fresh());

            $this->line("  - {$yayasan->nama}: Rp" . number_format($hasil['total'], 0, ',', '.'));

            if ($dryRun) {
                continue;
            }

            $planAksesPlatform = $yayasan->activeSubscription()?->plan;

            if (! $planAksesPlatform) {
                Log::warning("GenerateTenantInvoices: yayasan {$yayasan->id} lolos filter tapi tidak punya activeSubscription->plan, dilewati.");

                continue;
            }

            $subscription = $yayasan->subscriptions()->create([
                'subscription_plan_id' => $planAksesPlatform->id,
                'siklus_billing' => 'bulanan',
                'status' => 'pending',
                'computed_amount' => $hasil['total'],
                'computed_breakdown' => $hasil,
                'periode' => $periode,
            ]);

            try {
                $paymentUrl = $xendit->createTransaction(
                    $subscription,
                    $planAksesPlatform,
                    $yayasan->email ?? 'billing@qinaraindonesia.id'
                );
            } catch (\Throwable $e) {
                Log::error("GenerateTenantInvoices: gagal membuat transaksi Xendit untuk yayasan {$yayasan->id}: {$e->getMessage()}");
                $this->error("    Gagal membuat transaksi Xendit: {$e->getMessage()}");

                continue;
            }

            try {
                \App\Services\NotificationService::sendTagihanSubscription(
                    $yayasan,
                    $hasil['total'],
                    $paymentUrl,
                    $periode
                );
            } catch (\Throwable $e) {
                // Xendit SUDAH berhasil di sini -- gagal kirim WA bukan
                // alasan untuk gagalkan invoice-nya, cukup dicatat supaya
                // link tetap bisa dikirim manual lewat tombol "Link Tagihan".
                Log::error("GenerateTenantInvoices: transaksi Xendit sukses tapi notifikasi WA gagal untuk yayasan {$yayasan->id}: {$e->getMessage()}");
            }
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
