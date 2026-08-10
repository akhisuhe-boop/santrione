<?php

namespace App\Filament\Platform\Widgets;

use App\Models\Yayasan;
use Filament\Widgets\ChartWidget;

class PertumbuhanYayasanChart extends ChartWidget
{
    protected static ?string $heading = 'Pertumbuhan Yayasan (6 Bulan Terakhir)';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $bulanBerjalan = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $jumlahBaru = $bulanBerjalan->map(
            fn ($bulan) => Yayasan::withoutGlobalScopes()
                ->whereYear('created_at', $bulan->year)
                ->whereMonth('created_at', $bulan->month)
                ->count()
        );

        $kumulatif = $bulanBerjalan->map(
            fn ($bulan) => Yayasan::withoutGlobalScopes()
                ->where('created_at', '<=', $bulan->copy()->endOfMonth())
                ->count()
        );

        return [
            'datasets' => [
                [
                    'label' => 'Yayasan Baru',
                    'data' => $jumlahBaru->values()->all(),
                    'borderColor' => '#00A39D',
                    'backgroundColor' => 'rgba(0, 163, 157, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Total Kumulatif',
                    'data' => $kumulatif->values()->all(),
                    'borderColor' => '#D97706',
                    'fill' => false,
                ],
            ],
            'labels' => $bulanBerjalan->map(fn ($b) => $b->translatedFormat('M Y'))->values()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
