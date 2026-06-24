<?php

namespace App\Filament\Resources\PrestasiSiswaResource\Pages;

use App\Filament\Resources\PrestasiSiswaResource;
use Filament\Resources\Pages\CreateRecord;

use App\Services\NotificationService;

class CreatePrestasiSiswa extends CreateRecord
{
    protected static string $resource = PrestasiSiswaResource::class;

    protected function afterCreate(): void
    {
        NotificationService::sendPrestasi(
            $this->record->siswa,
            $this->record->prestasi
        );
    }
}