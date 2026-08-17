<?php

namespace App\Console\Commands;

use App\Models\Broadcast;
use App\Models\Lead;
use App\Models\Yayasan;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class KirimBroadcastTerjadwal extends Command
{
    protected $signature = 'crm:kirim-broadcast-terjadwal {--broadcast_id= : Kirim 1 broadcast tertentu langsung, abaikan jadwal}';

    protected $description = 'Kirim semua Broadcast berstatus terjadwal yang jadwal_kirim-nya sudah lewat/sekarang.';

    public function handle(): int
    {
        $query = Broadcast::where('status', 'terjadwal');

        if ($id = $this->option('broadcast_id')) {
            $query->where('id', $id);
        } else {
            $query->where('jadwal_kirim', '<=', now());
        }

        $broadcasts = $query->get();

        if ($broadcasts->isEmpty()) {
            $this->info('Tidak ada broadcast yang perlu dikirim saat ini.');

            return self::SUCCESS;
        }

        foreach ($broadcasts as $broadcast) {
            $this->kirimSatu($broadcast);
        }

        return self::SUCCESS;
    }

    protected function kirimSatu(Broadcast $broadcast): void
    {
        try {
            $nomorSet = collect();

            foreach ($broadcast->target_types as $target) {
                match ($target) {
                    'yayasan_semua' => $nomorSet = $nomorSet->concat(
                        Yayasan::whereNotNull('telepon')->pluck('telepon')
                    ),
                    'yayasan_trial' => $nomorSet = $nomorSet->concat(
                        Yayasan::where('status', 'trial')->whereNotNull('telepon')->pluck('telepon')
                    ),
                    'yayasan_aktif' => $nomorSet = $nomorSet->concat(
                        Yayasan::where('status', 'active')->whereNotNull('telepon')->pluck('telepon')
                    ),
                    'lead_semua' => $nomorSet = $nomorSet->concat(
                        Lead::whereNotNull('no_hp')->pluck('no_hp')
                    ),
                    default => null,
                };
            }

            $nomorUnik = $nomorSet->filter()->unique()->values();

            foreach ($nomorUnik as $nomor) {
                NotificationService::waPlatform($nomor, $broadcast->pesan);
            }

            $broadcast->update([
                'status' => 'terkirim',
                'dikirim_pada' => now(),
                'jumlah_terkirim' => $nomorUnik->count(),
            ]);

            $this->info("Broadcast #{$broadcast->id} '{$broadcast->judul}' terkirim ke {$nomorUnik->count()} nomor.");
        } catch (\Throwable $e) {
            $broadcast->update([
                'status' => 'gagal',
                'pesan_error' => $e->getMessage(),
            ]);

            $this->error("Broadcast #{$broadcast->id} gagal: {$e->getMessage()}");
        }
    }
}
