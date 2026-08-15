<?php

namespace App\Filament\Platform\Resources\EkosistemSolusiResource\Pages;

use App\Filament\Platform\Resources\EkosistemSolusiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEkosistemSolusi extends EditRecord
{
    protected static string $resource = EkosistemSolusiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
