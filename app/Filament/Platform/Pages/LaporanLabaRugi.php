<?php

namespace App\Filament\Platform\Pages;

use App\Models\QinaraKasTransaksi;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

/**
 * DITAMBAHKAN -- laporan laba-rugi SEDERHANA (bukan double-entry/
 * neraca) untuk pembukuan internal bisnis Qinara: total pemasukan
 * per kategori, total pengeluaran per kategori, dan laba/rugi bersih
 * pada rentang tanggal yang dipilih (default: bulan berjalan).
 */
class LaporanLabaRugi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Laba Rugi';
    protected static ?string $navigationGroup = 'Keuangan Qinara';
    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Laporan Laba Rugi Qinara';

    protected static string $view = 'filament.platform.pages.laporan-laba-rugi';

    public ?string $dari = null;
    public ?string $sampai = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public function mount(): void
    {
        $this->dari = now()->startOfMonth()->format('Y-m-d');
        $this->sampai = now()->endOfMonth()->format('Y-m-d');
    }

    public function getLaporan(): array
    {
        $dari = $this->dari ? Carbon::parse($this->dari)->startOfDay() : null;
        $sampai = $this->sampai ? Carbon::parse($this->sampai)->endOfDay() : null;

        $transaksis = QinaraKasTransaksi::with('kategori')
            ->when($dari, fn ($q) => $q->whereDate('tanggal', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('tanggal', '<=', $sampai))
            ->get();

        $buatRincian = function ($tipe) use ($transaksis) {
            return $transaksis
                ->where('tipe', $tipe)
                ->groupBy(fn ($t) => $t->kategori?->nama ?? '(Tanpa kategori)')
                ->map(fn ($grup, $namaKategori) => [
                    'kategori' => $namaKategori,
                    'total' => $grup->sum('nominal'),
                ])
                ->sortByDesc('total')
                ->values();
        };

        $rincianMasuk = $buatRincian('masuk');
        $rincianKeluar = $buatRincian('keluar');

        $totalMasuk = $rincianMasuk->sum('total');
        $totalKeluar = $rincianKeluar->sum('total');

        return [
            'rincian_masuk' => $rincianMasuk->toArray(),
            'rincian_keluar' => $rincianKeluar->toArray(),
            'total_masuk' => $totalMasuk,
            'total_keluar' => $totalKeluar,
            'laba_bersih' => $totalMasuk - $totalKeluar,
        ];
    }
}
