<?php

namespace App\Console\Commands;

use App\Models\Yayasan;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Kirim reminder WA H-5/H-3/H-1 ke Yayasan yang langganan berbayarnya
 * SUDAH lewat jatuh tempo (akses sudah dibatasi lewat
 * CheckExpiredSubscriptions sejak hari pertama) dan masih dalam
 * jendela masa tenggang (grace_period_days, default 7 hari sejak
 * jatuh tempo).
 *
 * BEDA dengan SendTrialReminders: itu untuk yayasan yang masih TRIAL
 * dan mengingatkan SEBELUM trial habis. Ini untuk yayasan yang
 * PERNAH/SEDANG berlangganan berbayar dan sudah TELAT perpanjang --
 * jadi disaring pakai whereHas('subscriptions') (bukan status trial)
 * supaya trial murni yang expired tidak dobel dapat reminder dari
 * command ini.
 *
 * Milestone dihitung mundur DARI AKHIR masa tenggang, bukan dari
 * tanggal jatuh tempo -- H-5 berarti "5 hari lagi sebelum genap
 * {grace_period_days} hari sejak jatuh tempo", dst. Kalau
 * grace_period_days di-set lebih kecil dari salah satu milestone
 * (mis. di-set 3 hari), milestone yang lebih besar dari itu otomatis
 * dilewati (tidak pernah terpicu, tidak error).
 *
 * Dijadwalkan harian, cek TANGGAL PERSIS (bukan "kurang dari") supaya
 * tiap Yayasan cuma dapat 1x reminder per milestone.
 */
class SendGracePeriodReminders extends Command
{
    protected $signature = 'subscription:send-grace-reminders';

    protected $description = 'Kirim reminder WA H-5/H-3/H-1 ke Yayasan berbayar yang telat perpanjang, masih dalam masa tenggang';

    public function handle(): int
    {
        $graceDays = (int) config('subscription.grace_period_days', 7);
        $milestones = [5, 3, 1];
        $totalTerkirim = 0;

        foreach ($milestones as $sisaHari) {
            // Berapa hari sejak jatuh tempo supaya "sisa hari sampai
            // akhir masa tenggang" persis sama dengan $sisaHari.
            $hariSejakJatuhTempo = $graceDays - $sisaHari;

            if ($hariSejakJatuhTempo < 0) {
                // grace_period_days di-set lebih pendek dari milestone
                // ini -- lewati, tidak relevan.
                continue;
            }

            $tanggalTarget = now()->subDays($hariSejakJatuhTempo)->toDateString();

            $yayasans = Yayasan::withoutGlobalScopes()
                ->where('status', 'suspended')
                ->whereHas('subscriptions')
                ->get()
                ->filter(function (Yayasan $yayasan) use ($tanggalTarget) {
                    $latest = $yayasan->subscriptions()
                        ->whereNotNull('berakhir_pada')
                        ->latest('id')
                        ->first();

                    return $latest && $latest->berakhir_pada->toDateString() === $tanggalTarget;
                });

            foreach ($yayasans as $yayasan) {
                try {
                    $latest = $yayasan->subscriptions()
                        ->whereNotNull('berakhir_pada')
                        ->latest('id')
                        ->first();

                    $terkirim = NotificationService::sendGracePeriodReminder($yayasan, $sisaHari, $latest->berakhir_pada);

                    if ($terkirim) {
                        $totalTerkirim++;
                        $this->line("  - {$yayasan->nama}: reminder masa tenggang H-{$sisaHari} terkirim");
                    }
                } catch (\Throwable $e) {
                    Log::error("SendGracePeriodReminders: gagal kirim reminder untuk yayasan {$yayasan->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Selesai. Total reminder terkirim: {$totalTerkirim}");

        return self::SUCCESS;
    }
}
