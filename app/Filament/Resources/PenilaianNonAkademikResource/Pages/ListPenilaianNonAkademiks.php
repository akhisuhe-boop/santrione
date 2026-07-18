<?php

namespace App\Filament\Resources\PenilaianNonAkademikResource\Pages;

use App\Filament\Resources\PenilaianNonAkademikResource;
use App\Filament\Concerns\HidesAlumniByDefault;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenilaianNonAkademiks extends ListRecords
{
    use HidesAlumniByDefault;

    protected static string $resource = PenilaianNonAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->alumniToggleAction(),
            Actions\CreateAction::make(),
        ];
    }
}
