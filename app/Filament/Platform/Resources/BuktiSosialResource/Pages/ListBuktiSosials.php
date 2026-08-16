<?php

namespace App\Filament\Platform\Resources\BuktiSosialResource\Pages;

use App\Filament\Platform\Resources\BuktiSosialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBuktiSosials extends ListRecords
{
    protected static string $resource = BuktiSosialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
