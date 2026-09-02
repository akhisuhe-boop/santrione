<?php

namespace App\Filament\Platform\Resources\KantinPengaturanResource\Pages;

use App\Filament\Platform\Resources\KantinPengaturanResource;
use Filament\Resources\Pages\ListRecords;

class ListKantinPengaturans extends ListRecords
{
    protected static string $resource = KantinPengaturanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
