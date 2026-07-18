<?php

namespace App\Filament\Resources\SiswaResource\Pages;

use App\Filament\Resources\SiswaResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;

class ListSiswas extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = SiswaResource::class;

    // Model List ini SENDIRI adalah Siswa
    protected ?string $alumniRelation = null;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
        ];
    }
}
