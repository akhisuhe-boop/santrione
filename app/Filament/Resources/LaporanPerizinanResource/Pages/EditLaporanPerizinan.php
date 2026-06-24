<?php

namespace App\Filament\Resources\LaporanPerizinanResource\Pages;

use App\Filament\Resources\LaporanPerizinanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanPerizinan extends EditRecord
{
    protected static string $resource = LaporanPerizinanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
