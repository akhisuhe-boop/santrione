<?php

namespace App\Filament\Resources\KantinProdukResource\Pages;

use App\Filament\Resources\KantinProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKantinProduk extends EditRecord
{
    protected static string $resource = KantinProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
