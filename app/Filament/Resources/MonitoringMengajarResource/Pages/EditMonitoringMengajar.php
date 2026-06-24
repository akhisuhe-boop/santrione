<?php

namespace App\Filament\Resources\MonitoringMengajarResource\Pages;

use App\Filament\Resources\MonitoringMengajarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringMengajar extends EditRecord
{
    protected static string $resource = MonitoringMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
