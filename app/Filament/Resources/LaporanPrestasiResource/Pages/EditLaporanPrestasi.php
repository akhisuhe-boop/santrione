<?php

namespace App\Filament\Resources\LaporanPrestasiResource\Pages;

use App\Filament\Resources\LaporanPrestasiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanPrestasi extends EditRecord
{
    protected static string $resource = LaporanPrestasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
