<?php

namespace App\Filament\Platform\Resources\MasalahSolusiResource\Pages;

use App\Filament\Platform\Resources\MasalahSolusiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasalahSolusis extends ListRecords
{
    protected static string $resource = MasalahSolusiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
