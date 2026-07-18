<?php

namespace App\Filament\Resources\LaporanPerizinanResource\Pages;

use App\Filament\Resources\LaporanPerizinanResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanPerizinans extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = LaporanPerizinanResource::class;

    // Model resource ini LANGSUNG Siswa
    protected ?string $alumniRelation = null;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
        ];
    }
}
