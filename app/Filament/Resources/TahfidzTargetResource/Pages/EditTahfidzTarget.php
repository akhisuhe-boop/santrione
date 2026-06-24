<?php

namespace App\Filament\Resources\TahfidzTargetResource\Pages;

use App\Filament\Resources\TahfidzTargetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTahfidzTarget extends EditRecord
{
    protected static string $resource = TahfidzTargetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
