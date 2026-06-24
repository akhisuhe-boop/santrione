<?php

namespace App\Filament\Resources\PrestasiSiswaResource\Pages;

use App\Filament\Resources\PrestasiSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrestasiSiswa extends EditRecord
{
    protected static string $resource = PrestasiSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
