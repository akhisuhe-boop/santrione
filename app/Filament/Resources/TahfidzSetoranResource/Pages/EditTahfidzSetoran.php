<?php

namespace App\Filament\Resources\TahfidzSetoranResource\Pages;

use App\Filament\Resources\TahfidzSetoranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTahfidzSetoran extends EditRecord
{
    protected static string $resource = TahfidzSetoranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
