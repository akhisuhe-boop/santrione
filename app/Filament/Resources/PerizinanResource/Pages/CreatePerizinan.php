<?php

namespace App\Filament\Resources\PerizinanResource\Pages;

use App\Filament\Resources\PerizinanResource;
use Filament\Resources\Pages\CreateRecord;

use App\Services\NotificationService;

class CreatePerizinan extends CreateRecord
{
    protected static string $resource = PerizinanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['tanggal_selesai'])) {

            $data['tanggal_selesai'] = \Carbon\Carbon::parse($data['tanggal_selesai'])
                ->setTime(17, 0, 0);

        }

        return $data;
    }

    protected function afterCreate(): void
    {
        NotificationService::sendPerizinanApproved(
            $this->record
        );
    }
}