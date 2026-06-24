<?php

namespace App\Filament\Resources\MonitoringMengajarResource\Pages;

use App\Filament\Resources\MonitoringMengajarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringMengajars extends ListRecords
{
    protected static string $resource = MonitoringMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
