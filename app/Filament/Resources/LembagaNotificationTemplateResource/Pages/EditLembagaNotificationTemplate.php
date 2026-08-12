<?php

namespace App\Filament\Resources\LembagaNotificationTemplateResource\Pages;

use App\Filament\Resources\LembagaNotificationTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLembagaNotificationTemplate extends EditRecord
{
    protected static string $resource = LembagaNotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
