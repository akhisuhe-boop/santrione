<?php

namespace App\Filament\Platform\Resources\BuktiSosialResource\Pages;

use App\Filament\Platform\Resources\BuktiSosialResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBuktiSosial extends EditRecord
{
    protected static string $resource = BuktiSosialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
