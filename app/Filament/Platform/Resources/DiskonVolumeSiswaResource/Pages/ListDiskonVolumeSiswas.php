<?php

namespace App\Filament\Platform\Resources\DiskonVolumeSiswaResource\Pages;

use App\Filament\Platform\Resources\DiskonVolumeSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiskonVolumeSiswas extends ListRecords
{
    protected static string $resource = DiskonVolumeSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
