<?php

namespace App\Filament\Platform\Resources\StudiKasusResource\Pages;

use App\Filament\Platform\Resources\StudiKasusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStudiKasus extends ListRecords
{
    protected static string $resource = StudiKasusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
