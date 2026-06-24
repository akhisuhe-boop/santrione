<?php

namespace App\Filament\Resources\KartuTemplateResource\Pages;

use App\Filament\Resources\KartuTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKartuTemplates extends ListRecords
{
    protected static string $resource = KartuTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
