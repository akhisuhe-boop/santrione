<?php

namespace App\Filament\Resources\YayasanResource\Pages;

use App\Filament\Resources\YayasanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditYayasan extends EditRecord
{
    protected static string $resource = YayasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
