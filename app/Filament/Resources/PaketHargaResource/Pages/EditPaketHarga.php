<?php

namespace App\Filament\Resources\PaketHargaResource\Pages;

use App\Filament\Resources\PaketHargaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaketHarga extends EditRecord
{
    protected static string $resource = PaketHargaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
