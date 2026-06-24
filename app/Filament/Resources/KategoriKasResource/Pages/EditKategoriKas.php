<?php

namespace App\Filament\Resources\KategoriKasResource\Pages;

use App\Filament\Resources\KategoriKasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKategoriKas extends EditRecord
{
    protected static string $resource = KategoriKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
