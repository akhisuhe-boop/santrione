<?php

namespace App\Filament\Resources\TahfidzSetoranResource\Pages;

use App\Filament\Resources\TahfidzSetoranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTahfidzSetorans extends ListRecords
{
    protected static string $resource = TahfidzSetoranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
