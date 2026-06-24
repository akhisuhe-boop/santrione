<?php

namespace App\Filament\Resources\PenilaianNonAkademikResource\Pages;

use App\Filament\Resources\PenilaianNonAkademikResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenilaianNonAkademiks extends ListRecords
{
    protected static string $resource = PenilaianNonAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
