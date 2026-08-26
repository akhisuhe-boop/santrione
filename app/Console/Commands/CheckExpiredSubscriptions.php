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

            $latest = $yayasan->subscriptions()
                ->whereNotNull('berakhir_pada')
                ->latest('berakhir_pada')
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
