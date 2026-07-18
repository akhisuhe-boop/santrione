<?php

namespace App\Filament\Resources\LaporanPelanggaranResource\Pages;

use App\Filament\Resources\LaporanPelanggaranResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLaporanPelanggarans extends ManageRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = LaporanPelanggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
