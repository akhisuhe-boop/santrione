<?php

namespace App\Filament\Platform\Resources\PlatformBroadcastResource\Pages;

use App\Filament\Platform\Resources\PlatformBroadcastResource;
use App\Models\PlatformBroadcast;
use App\Services\NotificationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePlatformBroadcast extends CreateRecord
{
    protected static string $resource = PlatformBroadcastResource::class;

    /**
     * Bukan sekadar simpan record — di sinilah pengiriman WA
     * sungguhan terjadi, sekali saat tombol submit ditekan.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $targetFilter = match ($data['target_tipe'] ?? 'semua') {
            'status' => ['tipe' => 'status', 'status' => $data['target_status'] ?? []],
            'manual' => ['tipe' => 'manual', 'yayasan_ids' => $data['target_yayasan_ids'] ?? []],
            default => ['tipe' => 'semua'],
        };

        $broadcast = PlatformBroadcast::create([
            'judul' => $data['judul'],
            'pesan' => $data['pesan'],
            'target_filter' => $targetFilter,
            'status' => 'draft',
            'dikirim_oleh' => auth()->id(),
        ]);

        $penerima = $broadcast->resolveTargetYayasan();

        $jumlahBerhasil = 0;

        foreach ($penerima as $yayasan) {
            $terkirim = NotificationService::sendBroadcastYayasan(
                $yayasan,
                $broadcast->judul,
                $broadcast->pesan
            );

            if ($terkirim) {
                $jumlahBerhasil++;
            }
        }

        $broadcast->update([
            'jumlah_penerima' => $penerima->count(),
            'jumlah_berhasil' => $jumlahBerhasil,
            'status' => $jumlahBerhasil === $penerima->count() ? 'terkirim' : 'gagal_sebagian',
            'dikirim_pada' => now(),
        ]);

        return $broadcast;
    }
}
