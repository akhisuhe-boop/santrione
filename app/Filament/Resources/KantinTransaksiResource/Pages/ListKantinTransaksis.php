<?php

namespace App\Filament\Resources\KantinTransaksiResource\Pages;

use App\Filament\Resources\KantinTransaksiResource;
use Filament\Resources\Pages\ListRecords;

class ListKantinTransaksis extends ListRecords
{
    protected static string $resource = KantinTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
