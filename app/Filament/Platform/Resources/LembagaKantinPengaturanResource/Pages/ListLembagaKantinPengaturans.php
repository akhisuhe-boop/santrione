<?php

namespace App\Filament\Platform\Resources\LembagaKantinPengaturanResource\Pages;

use App\Filament\Platform\Resources\LembagaKantinPengaturanResource;
use Filament\Resources\Pages\ListRecords;

class ListLembagaKantinPengaturans extends ListRecords
{
    protected static string $resource = LembagaKantinPengaturanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
