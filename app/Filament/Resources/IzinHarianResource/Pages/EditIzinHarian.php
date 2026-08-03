<?php

namespace App\Filament\Resources\IzinHarianResource\Pages;

use App\Filament\Resources\IzinHarianResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIzinHarian extends EditRecord
{
    protected static string $resource = IzinHarianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
