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
    }
}