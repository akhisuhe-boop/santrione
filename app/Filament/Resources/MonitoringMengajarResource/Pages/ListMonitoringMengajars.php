<?php

namespace App\Filament\Resources\MonitoringMengajarResource\Pages;

use App\Filament\Resources\MonitoringMengajarResource;
use App\Exports\RekapitulasiMengajarExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListMonitoringMengajars extends ListRecords
{
    protected static string $resource = MonitoringMengajarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn () => Excel::download(
                    new RekapitulasiMengajarExport,
                    'rekapitulasi-mengajar-' . now()->format('Y-m-d') . '.xlsx'
                )),
        ];
    }
}
