<?php

namespace App\Filament\Resources\AsramaResource\Pages;

use App\Filament\Resources\AsramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAsramas extends ListRecords
{
    protected static string $resource = AsramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
