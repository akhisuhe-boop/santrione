<?php

namespace App\Filament\Platform\Resources\ModulePriceResource\Pages;

use App\Filament\Platform\Resources\ModulePriceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModulePrice extends EditRecord
{
    protected static string $resource = ModulePriceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
