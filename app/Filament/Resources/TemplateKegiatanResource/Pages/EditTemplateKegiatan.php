<?php

namespace App\Filament\Resources\TemplateKegiatanResource\Pages;

use App\Filament\Resources\TemplateKegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTemplateKegiatan extends EditRecord
{
    protected static string $resource = TemplateKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
