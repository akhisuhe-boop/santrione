<?php

namespace App\Filament\Platform\Resources\FaqItemResource\Pages;

use App\Filament\Platform\Resources\FaqItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaqItem extends EditRecord
{
    protected static string $resource = FaqItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
