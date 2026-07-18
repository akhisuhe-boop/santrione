<?php

namespace App\Filament\Resources\PrestasiSiswaResource\Pages;

use App\Filament\Resources\PrestasiSiswaResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrestasiSiswas extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = PrestasiSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
