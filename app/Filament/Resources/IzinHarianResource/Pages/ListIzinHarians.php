<?php

namespace App\Filament\Resources\IzinHarianResource\Pages;

use App\Filament\Resources\IzinHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIzinHarians extends ListRecords
{
    protected static string $resource = IzinHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
