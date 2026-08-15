<?php

namespace App\Filament\Platform\Resources\EkosistemSolusiResource\Pages;

use App\Filament\Platform\Resources\EkosistemSolusiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEkosistemSolusis extends ListRecords
{
    protected static string $resource = EkosistemSolusiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
