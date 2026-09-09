<?php

namespace App\Filament\Platform\Resources\CetakKartuSiswaResource\Pages;

use App\Filament\Platform\Resources\CetakKartuSiswaResource;
use Filament\Resources\Pages\ListRecords;

class ListCetakKartuSiswas extends ListRecords
{
    protected static string $resource = CetakKartuSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
