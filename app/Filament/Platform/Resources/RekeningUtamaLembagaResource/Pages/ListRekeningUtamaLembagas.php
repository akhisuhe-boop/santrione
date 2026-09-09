<?php

namespace App\Filament\Platform\Resources\RekeningUtamaLembagaResource\Pages;

use App\Filament\Platform\Resources\RekeningUtamaLembagaResource;
use Filament\Resources\Pages\ListRecords;

class ListRekeningUtamaLembagas extends ListRecords
{
    protected static string $resource = RekeningUtamaLembagaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada CreateAction -- Lembaga dikelola dari panel
            // admin/tenant, resource ini murni untuk pemetaan rekening.
        ];
    }
}
