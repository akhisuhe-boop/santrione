<?php

namespace App\Filament\Resources\PerizinanResource\Pages;

use App\Filament\Resources\PerizinanResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerizinans extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = PerizinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
