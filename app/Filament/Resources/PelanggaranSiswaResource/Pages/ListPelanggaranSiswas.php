<?php

namespace App\Filament\Resources\PelanggaranSiswaResource\Pages;

use App\Filament\Resources\PelanggaranSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPelanggaranSiswas extends ListRecords
{
    protected static string $resource = PelanggaranSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
