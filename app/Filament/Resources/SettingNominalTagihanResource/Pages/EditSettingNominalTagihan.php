<?php

namespace App\Filament\Resources\SettingNominalTagihanResource\Pages;

use App\Filament\Resources\SettingNominalTagihanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSettingNominalTagihan extends EditRecord
{
    protected static string $resource = SettingNominalTagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
