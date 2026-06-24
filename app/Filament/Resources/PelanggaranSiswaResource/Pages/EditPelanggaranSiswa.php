<?php

namespace App\Filament\Resources\PelanggaranSiswaResource\Pages;

use App\Filament\Resources\PelanggaranSiswaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPelanggaranSiswa extends EditRecord
{
    protected static string $resource = PelanggaranSiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
