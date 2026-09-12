<?php

namespace App\Filament\Platform\Resources\QinaraKasKategoriResource\Pages;

use App\Filament\Platform\Resources\QinaraKasKategoriResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQinaraKasKategori extends EditRecord
{
    protected static string $resource = QinaraKasKategoriResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
