<?php

namespace App\Filament\Resources\KategoriKasResource\Pages;

use App\Filament\Resources\KategoriKasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKategoriKas extends ListRecords
{
    protected static string $resource = KategoriKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
