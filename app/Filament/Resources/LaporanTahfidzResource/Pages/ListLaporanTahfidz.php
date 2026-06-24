<?php

namespace App\Filament\Resources\LaporanTahfidzResource\Pages;

use App\Filament\Resources\LaporanTahfidzResource;
use Filament\Resources\Pages\ListRecords;
use App\Models\TahfidzSetoran;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ListLaporanTahfidz extends ListRecords
{
    protected static string $resource = LaporanTahfidzResource::class;

    protected function getFooterWidgets(): array
    {
        $query = TahfidzSetoran::query();

        return [
            StatsOverviewWidget::make([
                Stat::make('Total Ziyadah', (clone $query)->where('jenis', 'ziyadah')->count()),
                Stat::make('Total Ayat', (clone $query)->where('jenis', 'ziyadah')->sum('jumlah_ayat')),
                Stat::make('Total Murajaah', (clone $query)->where('jenis', 'murajaah')->count()),
                Stat::make('Rata Nilai', round((clone $query)->avg('nilai'), 1)),
            ])
        ];
    }
}