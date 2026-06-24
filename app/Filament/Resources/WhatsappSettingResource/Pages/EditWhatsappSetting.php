<?php

namespace App\Filament\Resources\WhatsappSettingResource\Pages;

use App\Filament\Resources\WhatsappSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWhatsappSetting extends EditRecord
{
    protected static string $resource = WhatsappSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
