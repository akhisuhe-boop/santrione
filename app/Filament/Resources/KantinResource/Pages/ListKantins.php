<?php

namespace App\Filament\Resources\KantinResource\Pages;

use App\Filament\Resources\KantinResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKantins extends ListRecords
{
    protected static string $resource = KantinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
