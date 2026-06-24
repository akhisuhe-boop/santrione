<?php

namespace App\Filament\Resources\JurnalMengajarResource\Pages;

use App\Filament\Resources\JurnalMengajarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJurnalMengajar extends EditRecord
{
    protected static string $resource = JurnalMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
