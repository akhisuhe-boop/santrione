<?php

namespace App\Filament\Platform\Resources\BroadcastResource\Pages;

use App\Filament\Platform\Resources\BroadcastResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBroadcast extends EditRecord
{
    protected static string $resource = BroadcastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
