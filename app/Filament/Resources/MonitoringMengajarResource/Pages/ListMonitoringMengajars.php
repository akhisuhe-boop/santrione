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
                ->action(function () {
                    [$mulai, $selesai] = MonitoringMengajarResource::resolvePeriode(
                        $this->tableFilters['periode'] ?? null
                    );

                    return Excel::download(
                        new RekapitulasiMengajarExport($mulai, $selesai),
                        'rekapitulasi-mengajar-' . $mulai . '-sd-' . $selesai . '.xlsx'
                    );
                }),
        ];
    }
}
