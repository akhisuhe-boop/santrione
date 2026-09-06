<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Dashboard cuma tampil kalau role user PUNYA setidaknya 1
     * permission yang dipakai widget-widget di bawah — supaya role
     * yang dibatasi total ke 1 modul saja (mis. "Kantin") tidak lihat
     * halaman Dashboard yang isinya bakal kosong melompong.
     */
    public static function canAccess(): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        return static::hasAnyDashboardPermission();
    }

    protected static function hasAnyDashboardPermission(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('view_any_siswa')
            || $user->can('page_InputNilai')
            || $user->can('page_RekapNilai')
            || $user->can('page_RaportSiswa')
            || $user->can('view_any_kas')
            || $user->can('page_LaporanKas')
            || $user->can('page_LaporanPembayaran')
            || $user->can('view_any_prestasi')
            || $user->can('view_any_prestasi::siswa')
            || $user->can('view_any_laporan::prestasi')
            || $user->can('view_any_pelanggaran')
            || $user->can('view_any_pelanggaran::siswa')
            || $user->can('view_any_laporan::pelanggaran')
            || $user->can('view_any_perizinan')
            || $user->can('view_any_laporan::perizinan')
            || $user->can('view_any_tahfidz::setoran')
            || $user->can('view_any_tahfidz::target')
            || $user->can('view_any_laporan::tahfidz')
            // DITAMBAHKAN 6 Sep 2026 -- daftar di atas cuma cek
            // permission resource/halaman, LUPA menyertakan permission
            // widget yang JUGA dipakai getWidgets() di bawah. Efeknya:
            // role yang CUMA dikasih akses widget tertentu (mis.
            // "Pengasuhan" yang cuma dikasih widget_SiswaPerLembaga,
            // tanpa permission resource/halaman apa pun di atas) ditolak
            // masuk Dashboard SAMA SEKALI, padahal harusnya cukup lihat
            // widget itu saja.
            || $user->can('widget_StatistikAkademik')
            || $user->can('widget_SiswaPerLembaga')
            || $user->can('widget_KeuanganOverview')
            || $user->can('widget_GrafikKeuanganChart')
            || $user->can('widget_SantriTahfidzTable')
            || $user->can('widget_SantriIzinTable')
            || $user->can('widget_SantriMelanggarTable')
            || $user->can('widget_SantriBerprestasiTable');
    }

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
            auth()->user()->can('page_RaportSiswa') ||
            auth()->user()->can('widget_StatistikAkademik')
        ) {
            $widgets[] = \App\Filament\Widgets\StatistikAkademik::class;
        }

        if (
            auth()->user()->can('view_any_siswa') ||
            auth()->user()->can('page_InputNilai') ||
            auth()->user()->can('page_RekapNilai') ||
            auth()->user()->can('page_RaportSiswa') ||
            auth()->user()->can('widget_SiswaPerLembaga')
        ) {
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
            auth()->user()->can('page_LaporanPembayaran') ||
            auth()->user()->can('widget_KeuanganOverview')
        ) {
            $widgets[] = \App\Filament\Widgets\KeuanganOverview::class;
        }

        if (
            auth()->user()->can('view_any_kas') ||
            auth()->user()->can('page_LaporanKas') ||
            auth()->user()->can('page_LaporanPembayaran') ||
            auth()->user()->can('widget_GrafikKeuanganChart')
        ) {
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
            auth()->user()->can('view_any_laporan::prestasi') ||
            auth()->user()->can('widget_SantriBerprestasiTable')
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
            auth()->user()->can('view_any_laporan::pelanggaran') ||
            auth()->user()->can('widget_SantriMelanggarTable')
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
            auth()->user()->can('view_any_laporan::perizinan') ||
            auth()->user()->can('widget_SantriIzinTable')
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
            auth()->user()->can('view_any_laporan::tahfidz') ||
            auth()->user()->can('widget_SantriTahfidzTable')
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