<?php

namespace App\Filament\Platform\Resources\PromoKodeResource\Pages;

use App\Filament\Platform\Resources\PromoKodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromoKode extends EditRecord
{
    protected static string $resource = PromoKodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
