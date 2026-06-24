<?php

namespace App\Filament\Resources\JenisTagihanResource\Pages;

use App\Filament\Resources\JenisTagihanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJenisTagihans extends ListRecords
{
    protected static string $resource = JenisTagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
