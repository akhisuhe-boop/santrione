<?php

namespace App\Filament\Widgets;

use App\Models\KantinTransaksi;
use App\Models\KantinTransaksiItem;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KantinOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function lembagaIds()
    {
        $tenant = Filament::getTenant();

        return $tenant
            ? \App\Models\Lembaga::where('yayasan_id', $tenant->id)->pluck('id')
            : collect();
    }

    protected function getStats(): array
    {
        $lembagaIds = $this->lembagaIds();

        $base = KantinTransaksi::withoutGlobalScopes()->whereIn('lembaga_id', $lembagaIds);

        $pemasukanHariIni = (clone $base)->whereDate('tanggal', today())->sum('total');
        $transaksiHariIni = (clone $base)->whereDate('tanggal', today())->count();

        $pemasukanBulanIni = (clone $base)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('total');

        $pemasukanBulanLalu = (clone $base)
            ->whereMonth('tanggal', now()->subMonth()->month)
            ->whereYear('tanggal', now()->subMonth()->year)
            ->sum('total');

        $trendBulanIni = $pemasukanBulanLalu > 0
            ? round((($pemasukanBulanIni - $pemasukanBulanLalu) / $pemasukanBulanLalu) * 100)
            : null;

        $produkTerlaris = KantinTransaksiItem::query()
            ->whereHas('transaksi', fn ($q) => $q->withoutGlobalScopes()->whereIn('lembaga_id', $lembagaIds))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('nama_produk, SUM(qty) as total_qty')
            ->groupBy('nama_produk')
            ->orderByDesc('total_qty')
            ->first();

        return [

            Stat::make('Pemasukan Hari Ini', 'Rp ' . number_format($pemasukanHariIni, 0, ',', '.'))
                ->description($transaksiHariIni . ' transaksi hari ini')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success')
                ->chart($this->grafik7Hari($lembagaIds)),

            Stat::make('Pemasukan Bulan Ini', 'Rp ' . number_format($pemasukanBulanIni, 0, ',', '.'))
                ->description(
                    $trendBulanIni === null
                        ? 'Belum ada pembanding bulan lalu'
                        : ($trendBulanIni >= 0 ? "+{$trendBulanIni}% dari bulan lalu" : "{$trendBulanIni}% dari bulan lalu")
                )
                ->descriptionIcon($trendBulanIni !== null && $trendBulanIni < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($trendBulanIni !== null && $trendBulanIni < 0 ? 'danger' : 'primary'),

            Stat::make('Produk Terlaris Bulan Ini', $produkTerlaris->nama_produk ?? '-')
                ->description(($produkTerlaris->total_qty ?? 0) . ' terjual')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

        ];
    }

    protected function grafik7Hari($lembagaIds): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {

            $tanggal = now()->subDays($i);

            $data[] = KantinTransaksi::withoutGlobalScopes()
                ->whereIn('lembaga_id', $lembagaIds)
                ->whereDate('tanggal', $tanggal)
                ->sum('total');
        }

        return $data;
    }
}
