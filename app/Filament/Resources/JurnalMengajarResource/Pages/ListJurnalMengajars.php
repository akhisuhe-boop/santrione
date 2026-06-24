<?php

namespace App\Filament\Resources\JurnalMengajarResource\Pages;

use App\Filament\Resources\JurnalMengajarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJurnalMengajars extends ListRecords
{
    protected static string $resource = JurnalMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
