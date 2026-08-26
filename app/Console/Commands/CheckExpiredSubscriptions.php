<?php

namespace App\Console\Commands;

use App\Models\Yayasan;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscription:check-expired';

    protected $description = 'Suspend yayasan yang masa trial-nya habis atau langganan berbayarnya sudah lewat jatuh tempo';

    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | TRIAL HABIS
        |--------------------------------------------------------------------------
        */

        $expiredTrials = Yayasan::withoutGlobalScopes()
            ->where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($expiredTrials as $yayasan) {
            $yayasan->update(['status' => 'suspended']);
            $this->line("  ✓ Trial habis, di-suspend: {$yayasan->nama}");
        }

        /*
        |--------------------------------------------------------------------------
        | LANGGANAN AKTIF TAPI SUDAH LEWAT TANGGAL BERAKHIR
        |--------------------------------------------------------------------------
        |
        | PERUBAHAN 7 Sep 2026: dulu ada toleransi +grace_period_days
        | sebelum di-suspend. Sekarang restriksi akses (sidebar cuma
        | menu Langganan, lewat Yayasan::hasAccess()) berlaku LANGSUNG
        | begitu jatuh tempo lewat -- grace_period_days sekarang cuma
        | dipakai buat jendela pengingat WA H-5/H-3/H-1
        | (SendGracePeriodReminders), bukan penunda restriksi ini.
        |
        | Hanya yayasan yang MEMANG punya riwayat Subscription yang
        | dievaluasi di sini — yayasan lama yang di-grandfather (status
        | 'active' tanpa baris subscription sama sekali) TIDAK disentuh,
        | supaya tidak ada yang ke-suspend tanpa pernah ada tagihan.
        */

        $activeYayasans = Yayasan::withoutGlobalScopes()
            ->where('status', 'active')
            ->whereHas('subscriptions')
            ->get();

        foreach ($activeYayasans as $yayasan) {

            // latest('id'), BUKAN latest('berakhir_pada') -- yayasan
            // bisa punya banyak baris Subscription dari waktu ke waktu
            // (tiap kali langganan lama sudah lewat tanggal berakhir
            // lalu diaktifkan ulang, baris BARU dibuat, bukan update
            // baris lama -- lihat Langganan::aktifkanPaketFull()).
            // Baris paling akhir DIBUAT belum tentu punya berakhir_pada
            // paling besar (mis. langganan lama tahunan yang sudah
            // ditinggalkan bisa "kelihatan" berakhir lebih jauh dari
            // langganan baru bulanan yang sedang aktif) -- jadi yang
            // benar adalah baris paling BARU DIBUAT (id terbesar), bukan
            // baris dengan tanggal berakhir terbesar.
            $latest = $yayasan->subscriptions()
                ->whereNotNull('berakhir_pada')
                ->latest('id')
                ->first();

            if (! $latest) {
                continue;
            }

            if ($latest->berakhir_pada->isPast()) {
                $yayasan->update(['status' => 'suspended']);
                $this->line("  ✓ Langganan lewat jatuh tempo, di-suspend: {$yayasan->nama}");
            }
        }

        $this->info('Selesai cek langganan.');

        return self::SUCCESS;
    }
}
