<?php

namespace App\Filament\Platform\Resources\CetakKartuPegawaiResource\Pages;

use App\Filament\Platform\Resources\CetakKartuPegawaiResource;
use Filament\Resources\Pages\ListRecords;

class ListCetakKartuPegawais extends ListRecords
{
    protected static string $resource = CetakKartuPegawaiResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
