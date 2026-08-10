<?php

namespace App\Filament\Platform\Widgets;

use App\Models\Yayasan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int
    {
        return 3;
    }

    /**
     * MRR (Monthly Recurring Revenue) dihitung dari totalTagihan()
     * subscription AKTIF tiap yayasan — SATU-SATUNYA sumber nominal
     * (sama seperti yang dipakai invoice sungguhan), bukan estimasi
     * terpisah yang bisa tidak sinkron.
     */
    protected function estimasiMrr(): int
    {
        return Yayasan::withoutGlobalScopes()
            ->where('status', 'active')
            ->with(['subscriptions' => fn ($q) => $q->where('status', 'active')
                ->where('berakhir_pada', '>', now())
                ->with('plan')
                ->latest('berakhir_pada'),
            ])
            ->get()
            ->sum(fn (Yayasan $y) => $y->subscriptions->first()?->totalTagihan() ?? 0);
    }

    protected function getStats(): array
    {
        $totalYayasan = Yayasan::withoutGlobalScopes()->count();
        $aktif = Yayasan::withoutGlobalScopes()->where('status', 'active')->count();
        $trial = Yayasan::withoutGlobalScopes()->where('status', 'trial')->count();
        $suspended = Yayasan::withoutGlobalScopes()->where('status', 'suspended')->count();

        $totalLembaga = \App\Models\Lembaga::withoutGlobalScopes()->count();
        $totalSiswa = \App\Models\Siswa::withoutGlobalScopes()->where('status_siswa', 'Aktif')->count();

        $mrr = $this->estimasiMrr();
        $rataRataPerYayasan = $aktif > 0 ? intdiv($mrr, $aktif) : 0;

        $yayasanBaruBulanIni = Yayasan::withoutGlobalScopes()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return [
            Stat::make('Total Yayasan', $totalYayasan)
                ->description("{$aktif} aktif · {$trial} trial · {$suspended} suspended")
                ->color('primary'),

            Stat::make('Estimasi MRR', 'Rp ' . number_format($mrr, 0, ',', '.'))
                ->description('Dari subscription aktif berjalan')
                ->color('success'),

            Stat::make('Rata-rata / Yayasan Aktif', 'Rp ' . number_format($rataRataPerYayasan, 0, ',', '.'))
                ->description('MRR ÷ jumlah Yayasan aktif')
                ->color('warning'),

            Stat::make('Total Lembaga', $totalLembaga)
                ->description('Seluruh platform')
                ->color('gray'),

            Stat::make('Total Siswa Aktif', number_format($totalSiswa, 0, ',', '.'))
                ->description('Seluruh platform')
                ->color('gray'),

            Stat::make('Yayasan Baru Bulan Ini', $yayasanBaruBulanIni)
                ->description(now()->translatedFormat('F Y'))
                ->color('info'),
        ];
    }
}
