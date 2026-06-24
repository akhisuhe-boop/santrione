<?php

namespace App\Filament\Resources\PenilaianNonAkademikResource\Pages;

use App\Filament\Resources\PenilaianNonAkademikResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPenilaianNonAkademik extends EditRecord
{
    protected static string $resource = PenilaianNonAkademikResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
