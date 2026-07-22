<?php

namespace App\Filament\Resources\PayrollAdjustmentTemplateResource\Pages;

use App\Filament\Resources\PayrollAdjustmentTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayrollAdjustmentTemplate extends EditRecord
{
    protected static string $resource = PayrollAdjustmentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
