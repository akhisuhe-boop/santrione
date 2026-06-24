<?php

namespace App\Filament\Resources\PengaturanGajiResource\Pages;

use App\Filament\Resources\PengaturanGajiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengaturanGaji extends EditRecord
{
    protected static string $resource = PengaturanGajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
