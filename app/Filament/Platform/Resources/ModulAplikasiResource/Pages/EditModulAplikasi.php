<?php

namespace App\Filament\Platform\Resources\ModulAplikasiResource\Pages;

use App\Filament\Platform\Resources\ModulAplikasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModulAplikasi extends EditRecord
{
    protected static string $resource = ModulAplikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
