<?php

namespace App\Filament\Resources\JamPelajaranResource\Pages;

use App\Filament\Resources\JamPelajaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJamPelajaran extends EditRecord
{
    protected static string $resource = JamPelajaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
