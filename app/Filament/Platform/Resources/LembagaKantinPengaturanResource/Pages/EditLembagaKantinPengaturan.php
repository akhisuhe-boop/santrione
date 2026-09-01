<?php

namespace App\Filament\Platform\Resources\LembagaKantinPengaturanResource\Pages;

use App\Filament\Platform\Resources\LembagaKantinPengaturanResource;
use Filament\Resources\Pages\EditRecord;

class EditLembagaKantinPengaturan extends EditRecord
{
    protected static string $resource = LembagaKantinPengaturanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
