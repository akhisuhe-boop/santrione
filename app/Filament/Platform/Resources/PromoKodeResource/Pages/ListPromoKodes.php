<?php

namespace App\Filament\Platform\Resources\PromoKodeResource\Pages;

use App\Filament\Platform\Resources\PromoKodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromoKodes extends ListRecords
{
    protected static string $resource = PromoKodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
