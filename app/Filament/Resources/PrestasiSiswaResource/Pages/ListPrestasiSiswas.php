<?php

namespace App\Filament\Resources\PrestasiSiswaResource\Pages;

use App\Filament\Resources\PrestasiSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrestasiSiswas extends ListRecords
{
    protected static string $resource = PrestasiSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
