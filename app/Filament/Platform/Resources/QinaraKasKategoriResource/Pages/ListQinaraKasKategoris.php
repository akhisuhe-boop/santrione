<?php

namespace App\Filament\Platform\Resources\QinaraKasKategoriResource\Pages;

use App\Filament\Platform\Resources\QinaraKasKategoriResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQinaraKasKategoris extends ListRecords
{
    protected static string $resource = QinaraKasKategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
