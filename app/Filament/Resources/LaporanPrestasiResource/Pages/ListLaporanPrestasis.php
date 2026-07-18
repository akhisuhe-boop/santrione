<?php

namespace App\Filament\Resources\LaporanPrestasiResource\Pages;

use App\Filament\Resources\LaporanPrestasiResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanPrestasis extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = LaporanPrestasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
