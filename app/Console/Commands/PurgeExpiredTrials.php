<?php

namespace App\Console\Commands;

use App\Models\Yayasan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Hapus permanen Yayasan yang trial-nya sudah habis >14 hari DAN
 * TIDAK PERNAH punya pembayaran berhasil sama sekali (murni trial
 * yang tidak lanjut, bukan pelanggan yang pernah bayar lalu churn).
 *
 * KENAPA CEK "pernah bayar", BUKAN CUMA status='suspended':
 * CheckExpiredSubscriptions (command lain yang sudah ada) men-suspend
 * DUA jenis yayasan yang beda: (a) trial habis tanpa pernah bayar --
 * INI yang boleh dihapus, dan (b) pelanggan yang PERNAH bayar tapi
 * langganannya lewat masa tenggang tanpa diperpanjang (churn) --
 * INI TIDAK BOLEH dihapus, riwayat & datanya harus tetap ada. Kedua
 * jenis ini sama-sama berstatus 'suspended', jadi status saja tidak
 * cukup -- harus dicek riwayat SubscriptionPayment.status='berhasil'
 * (konsisten dipakai di DOKU/Xendit/verifikasi manual,
 * lihat commit sebelumnya).
 *
 * Dijadwalkan mingguan (bukan harian) -- penghapusan permanen,
 * sengaja tidak perlu buru-buru, cukup 1x seminggu.
 */
class PurgeExpiredTrials extends Command
{
    protected $signature = 'subscription:purge-expired-trials {--dry-run : Tampilkan saja yang akan dihapus, jangan benar-benar hapus}';

    protected $description = 'Hapus permanen Yayasan trial yang habis >14 hari dan tidak pernah bayar sama sekali';

    public function handle(): int
    {
        $batasHari = config('subscription.purge_trial_after_days', 14);
        $dryRun = (bool) $this->option('dry-run');

        $kandidat = Yayasan::withoutGlobalScopes()
            ->where('status', 'suspended')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now()->subDays($batasHari))
            ->whereDoesntHave('subscriptions.payments', function ($q) {
                $q->where('status', 'berhasil');
            })
            ->get();

        $this->info("Ditemukan {$kandidat->count()} Yayasan trial kadaluarsa >{$batasHari} hari, belum pernah bayar" . ($dryRun ? ' (dry-run, tidak dihapus)' : ''));

        foreach ($kandidat as $yayasan) {
            $jumlahUser = \App\Models\User::withoutGlobalScopes()->where('yayasan_id', $yayasan->id)->count();
            $this->line("  - {$yayasan->nama} (trial berakhir {$yayasan->trial_ends_at->toDateString()}, {$yayasan->lembagas()->count()} lembaga, {$jumlahUser} user)");

            if ($dryRun) {
                continue;
            }

            try {
                DB::transaction(function () use ($yayasan) {
                    // Hapus berurutan dari anak ke induk -- lebih
                    // eksplisit & aman daripada mengandalkan cascade
                    // delete di semua tabel (tidak semua FK di skema
                    // ini pasti onDelete cascade).
                    foreach ($yayasan->lembagas as $lembaga) {
                        $lembaga->modules()->delete();
                        $lembaga->delete();
                    }

                    $yayasan->subscriptions()->each(function ($sub) {
                        $sub->payments()->delete();
                        $sub->delete();
                    });

                    \App\Models\User::withoutGlobalScopes()
                        ->where('yayasan_id', $yayasan->id)
                        ->delete();

                    $yayasan->delete();
                });

                $this->line('    -> dihapus permanen');
            } catch (\Throwable $e) {
                Log::error("PurgeExpiredTrials: gagal hapus yayasan {$yayasan->id}: {$e->getMessage()}");
                $this->error("    -> GAGAL dihapus: {$e->getMessage()}");
            }
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
