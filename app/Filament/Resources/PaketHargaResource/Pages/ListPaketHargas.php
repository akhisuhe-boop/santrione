<?php

namespace App\Filament\Resources\PaketHargaResource\Pages;

use App\Filament\Resources\PaketHargaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaketHargas extends ListRecords
{
    protected static string $resource = PaketHargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
