<?php

namespace App\Filament\Resources\KantinProdukResource\Pages;

use App\Filament\Resources\KantinProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKantinProduks extends ListRecords
{
    protected static string $resource = KantinProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
