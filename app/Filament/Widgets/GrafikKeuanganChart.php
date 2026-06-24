<?php

namespace App\Filament\Widgets;

use App\Models\Kas;
use Filament\Widgets\ChartWidget;

class GrafikKeuanganChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Cashflow Bulanan';
    protected static ?string $maxHeight = '320px';
    public static function canView(): bool
    {
        return auth()->user()->can('view_any_kas')
            || auth()->user()->can('view_any_pembayaran')
            || auth()->user()->can('page_LaporanKas')
            || auth()->user()->can('page_LaporanPembayaran');
    }
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected function getData(): array
    {
        $bulan = [];
        $pemasukan = [];
        $pengeluaran = [];

        for ($i = 1; $i <= 12; $i++) {

            $bulan[] = date('M', mktime(0, 0, 0, $i, 1));

            $pemasukan[] = Kas::query()
                ->where('tipe', 'masuk')
                ->whereMonth('tanggal', $i)
                ->whereYear('tanggal', now()->year)
                ->sum('nominal');

            $pengeluaran[] = Kas::query()
                ->where('tipe', 'keluar')
                ->whereMonth('tanggal', $i)
                ->whereYear('tanggal', now()->year)
                ->sum('nominal');
        }

        return [
            'datasets' => [

                [
                    'label' => 'Pemasukan',
                    'data' => $pemasukan,
                    'borderColor' => '#10b981',
                    'backgroundColor' => '#10b981',
                ],

                [
                    'label' => 'Pengeluaran',
                    'data' => $pengeluaran,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => '#ef4444',
                ],

            ],

            'labels' => $bulan,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}