<?php

namespace App\Filament\Widgets;

use App\Models\Siswa;
use App\Models\Pegawai;
use App\Models\Lembaga;
use App\Models\Kelas;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikAkademik extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    /*
    |--------------------------------------------------------------------------
    | VISIBILITY
    |--------------------------------------------------------------------------
    */

    public static function canView(): bool
    {
        return auth()->user()->can('view_any_siswa')
            || auth()->user()->can('page_InputNilai')
            || auth()->user()->can('page_RekapNilai')
            || auth()->user()->can('page_RaportSiswa');
    }

    /*
    |--------------------------------------------------------------------------
    | STATS
    |--------------------------------------------------------------------------
    */

    protected function getStats(): array
    {
        return [

            // =====================================================
            // TOTAL SISWA
            // =====================================================

            Stat::make(
                'Total Data Siswa',
                number_format(Siswa::where('status_siswa', 'Aktif')->count())
            )
                ->description('Jumlah seluruh siswa')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            // =====================================================
            // TOTAL GURU & PEGAWAI
            // =====================================================

            Stat::make(
                'Total Guru',
                number_format(Pegawai::count())
            )
                ->description('Jumlah seluruh guru & pegawai')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            // =====================================================
            // TOTAL LEMBAGA
            // =====================================================

            Stat::make(
                'Total Lembaga',
                number_format(Lembaga::count())
            )
                ->description('Jumlah lembaga aktif')
                ->descriptionIcon('heroicon-m-building-library')
                ->color('warning'),

            // =====================================================
            // TOTAL KELAS
            // =====================================================

            Stat::make(
                'Total Kelas',
                number_format(Kelas::count())
            )
                ->description('Jumlah seluruh kelas')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('primary'),

        ];
    }
}