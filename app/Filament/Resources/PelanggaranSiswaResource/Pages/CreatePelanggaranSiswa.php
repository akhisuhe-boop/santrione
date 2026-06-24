<?php

namespace App\Filament\Resources\PelanggaranSiswaResource\Pages;

use App\Filament\Resources\PelanggaranSiswaResource;
use Filament\Resources\Pages\CreateRecord;

use App\Services\NotificationService;

class CreatePelanggaranSiswa extends CreateRecord
{
    protected static string $resource = PelanggaranSiswaResource::class;

    protected function afterCreate(): void
    {
        NotificationService::sendPelanggaran(
            $this->record->siswa,
            $this->record->pelanggaran
        );
    }
}