<?php

namespace App\Filament\Resources\JenisTagihanResource\Pages;

use App\Filament\Resources\JenisTagihanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJenisTagihan extends EditRecord
{
    protected static string $resource = JenisTagihanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
