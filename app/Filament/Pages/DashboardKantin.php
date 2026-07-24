<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\KantinOverview;
use App\Filament\Widgets\ProdukTerlarisKantin;
use App\Filament\Widgets\TransaksiTerbaruKantin;
use Filament\Facades\Filament;
use Filament\Pages\Page;

class DashboardKantin extends Page
{
    protected static ?string $navigationGroup = 'e-Kantin';
    protected static ?string $navigationLabel = 'Dashboard Kantin';
    protected static ?string $title = 'Dashboard Kantin';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?int $navigationSort = 0;

    // Halaman ini sepenuhnya dirender dari widget native Filament
    // (StatsOverview + Table) — lihat getHeaderWidgets() di bawah.
    protected static string $view = 'filament.pages.dashboard-kantin';

    public static function canAccess(): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        return (bool) Filament::getTenant()?->hasFeature(\App\Support\FeatureGate::E_KANTIN);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KantinOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ProdukTerlarisKantin::class,
            TransaksiTerbaruKantin::class,
        ];
    }
}
