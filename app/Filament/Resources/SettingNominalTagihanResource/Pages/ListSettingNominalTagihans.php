<?php

namespace App\Filament\Resources\SettingNominalTagihanResource\Pages;

use App\Filament\Resources\SettingNominalTagihanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSettingNominalTagihans extends ListRecords
{
    protected static string $resource = SettingNominalTagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
