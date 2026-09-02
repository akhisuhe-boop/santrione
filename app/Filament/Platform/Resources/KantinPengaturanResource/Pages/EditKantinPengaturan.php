<?php

namespace App\Filament\Platform\Resources\KantinPengaturanResource\Pages;

use App\Filament\Platform\Resources\KantinPengaturanResource;
use Filament\Resources\Pages\EditRecord;

class EditKantinPengaturan extends EditRecord
{
    protected static string $resource = KantinPengaturanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
