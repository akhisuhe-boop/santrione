<?php

namespace App\Filament\Platform\Resources\LembagaRekeningResource\Pages;

use App\Filament\Platform\Resources\LembagaRekeningResource;
use Filament\Resources\Pages\ListRecords;

class ListLembagaRekenings extends ListRecords
{
    protected static string $resource = LembagaRekeningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada CreateAction -- pendaftaran kategori baru tetap
            // lewat "+ Daftarkan Rekening Kategori Lain" di Edit Lembaga.
        ];
    }
}
