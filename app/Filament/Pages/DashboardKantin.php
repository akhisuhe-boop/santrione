<?php

namespace App\Filament\Pages;

use App\Models\KantinTransaksi;
use App\Models\KantinTransaksiItem;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class DashboardKantin extends Page
{
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?string $navigationLabel = 'Dashboard Kantin';
    protected static ?string $title = 'Dashboard Kantin';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.dashboard-kantin';

    public static function canAccess(): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        return (bool) Filament::getTenant()?->hasFeature(\App\Support\FeatureGate::E_KANTIN);
    }

    public array $data = [];

    public function mount(): void
    {
        $tenant = Filament::getTenant();
        $lembagaIds = $tenant
            ? \App\Models\Lembaga::where('yayasan_id', $tenant->id)->pluck('id')
            : collect();

        $baseQuery = KantinTransaksi::withoutGlobalScopes()
            ->whereIn('lembaga_id', $lembagaIds);

        $pemasukanHariIni = (clone $baseQuery)->whereDate('tanggal', today())->sum('total');
        $pemasukanBulanIni = (clone $baseQuery)
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('total');

        $transaksiHariIni = (clone $baseQuery)->whereDate('tanggal', today())->count();

        $produkTerlaris = KantinTransaksiItem::query()
            ->whereHas('transaksi', fn ($q) => $q->withoutGlobalScopes()->whereIn('lembaga_id', $lembagaIds))
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('nama_produk, SUM(qty) as total_qty, SUM(subtotal) as total_omzet')
            ->groupBy('nama_produk')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $transaksiTerbaru = (clone $baseQuery)
            ->with(['siswa', 'items'])
            ->latest('tanggal')
            ->limit(10)
            ->get();

        $this->data = [
            'pemasukan_hari_ini' => $pemasukanHariIni,
            'pemasukan_bulan_ini' => $pemasukanBulanIni,
            'transaksi_hari_ini' => $transaksiHariIni,
            'produk_terlaris' => $produkTerlaris,
            'transaksi_terbaru' => $transaksiTerbaru,
        ];
    }
}
