<?php

namespace App\Filament\Resources\StudiKasusResource\Pages;

use App\Filament\Resources\StudiKasusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudiKasus extends EditRecord
{
    protected static string $resource = StudiKasusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
