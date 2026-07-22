<?php

namespace App\Filament\Resources\PayrollAdjustmentTemplateResource\Pages;

use App\Filament\Resources\PayrollAdjustmentTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayrollAdjustmentTemplates extends ListRecords
{
    protected static string $resource = PayrollAdjustmentTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
