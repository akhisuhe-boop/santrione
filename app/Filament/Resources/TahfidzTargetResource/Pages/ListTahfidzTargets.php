<?php

namespace App\Filament\Resources\TahfidzTargetResource\Pages;

use App\Filament\Resources\TahfidzTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTahfidzTargets extends ListRecords
{
    protected static string $resource = TahfidzTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
