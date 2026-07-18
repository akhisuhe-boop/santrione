<?php

namespace App\Filament\Resources\PelanggaranSiswaResource\Pages;

use App\Filament\Resources\PelanggaranSiswaResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPelanggaranSiswas extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = PelanggaranSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
