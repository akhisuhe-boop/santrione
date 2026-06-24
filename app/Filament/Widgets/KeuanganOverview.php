<?php

namespace App\Filament\Widgets;

use App\Models\Kas;
use App\Models\Lembaga;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KeuanganOverview extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    public static function canView(): bool
    {
        return auth()->user()->can('view_any_kas')
            || auth()->user()->can('view_any_pembayaran')
            || auth()->user()->can('view_any_tagihan')
            || auth()->user()->can('page_LaporanKas')
            || auth()->user()->can('page_LaporanPembayaran');
    }
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $stats = [];

        // =====================================================
        // PEMASUKAN HARI INI
        // =====================================================

        $pemasukanHariIni = Kas::query()
            ->where('tipe', 'masuk')
            ->whereDate('tanggal', today())
            ->sum('nominal');

        // =====================================================
        // PENGELUARAN HARI INI
        // =====================================================

        $pengeluaranHariIni = Kas::query()
            ->where('tipe', 'keluar')
            ->whereDate('tanggal', today())
            ->sum('nominal');

        // =====================================================
        // PEMASUKAN BULAN INI
        // =====================================================

        $pemasukanBulanIni = Kas::query()
            ->where('tipe', 'masuk')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('nominal');

        // =====================================================
        // PENGELUARAN BULAN INI
        // =====================================================

        $pengeluaranBulanIni = Kas::query()
            ->where('tipe', 'keluar')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('nominal');

        // =====================================================
        // TOTAL TAGIHAN
        // =====================================================

        $totalTagihan = Tagihan::query()
            ->sum('nominal');

        // =====================================================
        // TOTAL DIBAYAR
        // =====================================================

        $totalDibayar = Pembayaran::query()
            ->sum('nominal');

        // =====================================================
        // TOTAL PEMASUKAN
        // =====================================================

        $totalPemasukan = Kas::query()
            ->where('tipe', 'masuk')
            ->sum('nominal');

        // =====================================================
        // TOTAL TUNGGAKAN
        // =====================================================

        $totalTunggakan = $totalTagihan - $totalDibayar;

        // =====================================================
        // CARD PEMASUKAN HARI INI
        // =====================================================

        $stats[] = Stat::make(
            'Pemasukan Hari Ini',
            'Rp ' . number_format($pemasukanHariIni, 0, ',', '.')
        )
            ->description('Total pemasukan hari ini')
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success');

        // =====================================================
        // CARD PENGELUARAN HARI INI
        // =====================================================

        $stats[] = Stat::make(
            'Pengeluaran Hari Ini',
            'Rp ' . number_format($pengeluaranHariIni, 0, ',', '.')
        )
            ->description('Total pengeluaran hari ini')
            ->descriptionIcon('heroicon-m-arrow-trending-down')
            ->color('danger');

        // =====================================================
        // CARD PEMASUKAN BULAN INI
        // =====================================================

        $stats[] = Stat::make(
            'Pemasukan Bulan Ini',
            'Rp ' . number_format($pemasukanBulanIni, 0, ',', '.')
        )
            ->description('Total pemasukan bulan ini')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('primary');

        // =====================================================
        // CARD PENGELUARAN BULAN INI
        // =====================================================

        $stats[] = Stat::make(
            'Pengeluaran Bulan Ini',
            'Rp ' . number_format($pengeluaranBulanIni, 0, ',', '.')
        )
            ->description('Total pengeluaran bulan ini')
            ->descriptionIcon('heroicon-m-credit-card')
            ->color('warning');

        // =====================================================
        // CARD TOTAL PEMASUKAN
        // =====================================================

        $stats[] = Stat::make(
            'Total Pemasukan',
            'Rp ' . number_format($totalPemasukan, 0, ',', '.')
        )
            ->description('Total seluruh pemasukan')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('success');

        // =====================================================
        // CARD TOTAL TUNGGAKAN
        // =====================================================

        $stats[] = Stat::make(
            'Total Tunggakan',
            'Rp ' . number_format($totalTunggakan, 0, ',', '.')
        )
            ->description('Total seluruh tunggakan siswa')
            ->descriptionIcon('heroicon-m-exclamation-circle')
            ->color('danger');

        return $stats;
    }
}