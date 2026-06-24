<?php

namespace App\Filament\Resources\LaporanPerizinanResource\Pages;

use App\Filament\Resources\LaporanPerizinanResource;
use Filament\Resources\Pages\CreateRecord;

use App\Services\NotificationService;

class CreateLaporanPerizinan extends CreateRecord
{
    protected static string $resource = LaporanPerizinanResource::class;

    protected function afterCreate(): void
    {
        NotificationService::sendPerizinan(
            $this->record->siswa,
            $this->record
        );
    }
}