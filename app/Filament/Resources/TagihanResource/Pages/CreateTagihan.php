<?php

namespace App\Filament\Resources\TagihanResource\Pages;

use App\Filament\Resources\TagihanResource;
use Filament\Resources\Pages\CreateRecord;

use App\Services\NotificationService;

class CreateTagihan extends CreateRecord
{
    protected static string $resource = TagihanResource::class;

    protected function afterCreate(): void
    {
        NotificationService::sendTagihan(
            $this->record->siswa,
            $this->record
        );
    }
}