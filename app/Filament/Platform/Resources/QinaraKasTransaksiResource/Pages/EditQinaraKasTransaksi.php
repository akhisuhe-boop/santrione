<?php

namespace App\Filament\Platform\Resources\QinaraKasTransaksiResource\Pages;

use App\Filament\Platform\Resources\QinaraKasTransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQinaraKasTransaksi extends EditRecord
{
    protected static string $resource = QinaraKasTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
