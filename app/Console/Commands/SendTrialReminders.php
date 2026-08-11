<?php

namespace App\Console\Commands;

use App\Models\Yayasan;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Kirim reminder WA ke Yayasan yang trial-nya akan habis dalam
 * 7, 3, atau 1 hari -- pengganti "persingkat trial jadi 7 hari"
 * (tetap 14 hari, tapi dengan urgensi bertahap lewat reminder).
 *
 * Dijadwalkan harian, cek trial_ends_at persis H-7/H-3/H-1 (bukan
 * "kurang dari") supaya tiap Yayasan cuma dapat 1x reminder per
 * milestone, tidak berulang tiap hari selama rentang itu.
 */
class SendTrialReminders extends Command
{
    protected $signature = 'subscription:send-trial-reminders';

    protected $description = 'Kirim reminder WA H-7/H-3/H-1 ke Yayasan yang masa trial-nya akan segera berakhir';

    public function handle(): int
    {
        $milestones = [7, 3, 1];
        $totalTerkirim = 0;

        foreach ($milestones as $sisaHari) {
            $tanggalTarget = now()->addDays($sisaHari)->toDateString();

            $yayasans = Yayasan::withoutGlobalScopes()
                ->where('status', 'trial')
                ->whereDate('trial_ends_at', $tanggalTarget)
                ->get();

            foreach ($yayasans as $yayasan) {
                try {
                    $terkirim = NotificationService::sendTrialReminder($yayasan, $sisaHari);

                    if ($terkirim) {
                        $totalTerkirim++;
                        $this->line("  - {$yayasan->nama}: reminder H-{$sisaHari} terkirim");
                    }
                } catch (\Throwable $e) {
                    Log::error("SendTrialReminders: gagal kirim reminder untuk yayasan {$yayasan->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Selesai. Total reminder terkirim: {$totalTerkirim}");

        return self::SUCCESS;
    }
}
