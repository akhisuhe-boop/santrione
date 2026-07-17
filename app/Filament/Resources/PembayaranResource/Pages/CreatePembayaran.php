<?php

namespace App\Filament\Resources\PembayaranResource\Pages;

use App\Filament\Resources\PembayaranResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\NotificationService;

class CreatePembayaran extends CreateRecord
{
    protected static string $resource = PembayaranResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tanggal_bayar'] = now();

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record->siswa ?? $this->record->ppdb;

        NotificationService::sendPembayaran(
            $user,
            $this->record
        );

        // Peringatan visual di layar admin (bukan WA) kalau ternyata
        // yang dibayar ini tagihan milik siswa yang sudah lulus/pindah --
        // supaya admin sadar dan bisa double-check ini memang disengaja
        // (misal nagih piutang alumni), bukan salah pilih siswa.
        if ($this->record->siswa && $this->record->siswa->status_siswa !== 'Aktif') {
            \Filament\Notifications\Notification::make()
                ->title('Perhatian: Siswa Alumni')
                ->body(
                    $this->record->siswa->nama_lengkap
                    . ' berstatus "' . $this->record->siswa->status_siswa . '"'
                    . ' (bukan siswa aktif). Pastikan pembayaran ini memang disengaja.'
                )
                ->warning()
                ->persistent()
                ->send();
        }
    }
}