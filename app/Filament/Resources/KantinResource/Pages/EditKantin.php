<?php

namespace App\Filament\Resources\KantinResource\Pages;

use App\Filament\Resources\KantinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKantin extends EditRecord
{
    protected static string $resource = KantinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
