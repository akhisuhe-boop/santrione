<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        $widgets = [];

        /*
        |--------------------------------------------------------------------------
        | GLOBAL WIDGET
        |--------------------------------------------------------------------------
        */

        //$widgets[] = \App\Filament\Widgets\GreetingWidget::class;

        /*
        |--------------------------------------------------------------------------
        | AKADEMIK
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->can('view_any_siswa') ||
            auth()->user()->can('page_InputNilai') ||
            auth()->user()->can('page_RekapNilai') ||
            auth()->user()->can('page_RaportSiswa')
        ) {

            $widgets[] = \App\Filament\Widgets\StatistikAkademik::class;

            $widgets[] = \App\Filament\Widgets\SiswaPerLembaga::class;
        }

        /*
        |--------------------------------------------------------------------------
        | KEUANGAN
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->can('view_any_kas') ||
            auth()->user()->can('page_LaporanKas') ||
            auth()->user()->can('page_LaporanPembayaran')
        ) {

            $widgets[] = \App\Filament\Widgets\KeuanganOverview::class;

            $widgets[] = \App\Filament\Widgets\GrafikKeuanganChart::class;
        }

        /*
        |--------------------------------------------------------------------------
        | PRESTASI
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->can('view_any_prestasi') ||
            auth()->user()->can('view_any_prestasi::siswa') ||
            auth()->user()->can('view_any_laporan::prestasi')
        ) {

            $widgets[] = \App\Filament\Widgets\SantriBerprestasiTable::class;
        }

        /*
        |--------------------------------------------------------------------------
        | PELANGGARAN
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->can('view_any_pelanggaran') ||
            auth()->user()->can('view_any_pelanggaran::siswa') ||
            auth()->user()->can('view_any_laporan::pelanggaran')
        ) {

            $widgets[] = \App\Filament\Widgets\SantriMelanggarTable::class;
        }

        /*
        |--------------------------------------------------------------------------
        | PERIZINAN
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->can('view_any_perizinan') ||
            auth()->user()->can('view_any_laporan::perizinan')
        ) {

            $widgets[] = \App\Filament\Widgets\SantriIzinTable::class;
        }

        /*
        |--------------------------------------------------------------------------
        | TAHFIDZ
        |--------------------------------------------------------------------------
        */

        if (
            auth()->user()->can('view_any_tahfidz::setoran') ||
            auth()->user()->can('view_any_tahfidz::target') ||
            auth()->user()->can('view_any_laporan::tahfidz')
        ) {

            $widgets[] = \App\Filament\Widgets\SantriTahfidzTable::class;
        }

        /*
        |--------------------------------------------------------------------------
        | QUICK ACTION
        |--------------------------------------------------------------------------
        */

        //$widgets[] = \App\Filament\Widgets\QuickActionWidget::class;

        return $widgets;
    }
}