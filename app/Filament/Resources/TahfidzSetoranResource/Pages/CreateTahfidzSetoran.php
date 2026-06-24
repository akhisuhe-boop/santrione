<?php

namespace App\Filament\Resources\TahfidzSetoranResource\Pages;

use App\Filament\Resources\TahfidzSetoranResource;
use Filament\Resources\Pages\CreateRecord;

use App\Services\NotificationService;

class CreateTahfidzSetoran extends CreateRecord
{
    protected static string $resource = TahfidzSetoranResource::class;

    protected function afterCreate(): void
    {
        NotificationService::sendTahfidz(
            $this->record->siswa,
            $this->record
        );
    }
}