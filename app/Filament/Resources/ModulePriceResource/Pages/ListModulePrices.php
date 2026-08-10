<?php

namespace App\Filament\Resources\ModulePriceResource\Pages;

use App\Filament\Resources\ModulePriceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModulePrices extends ListRecords
{
    protected static string $resource = ModulePriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
