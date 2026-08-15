<?php

namespace App\Filament\Resources\MockupScreenshotResource\Pages;

use App\Filament\Resources\MockupScreenshotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMockupScreenshot extends EditRecord
{
    protected static string $resource = MockupScreenshotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
