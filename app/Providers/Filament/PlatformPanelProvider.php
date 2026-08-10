<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsurePlatformAdmin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Panel KHUSUS Platform Admin — data lintas-Yayasan (MRR, daftar
 * semua Yayasan, harga modul, verifikasi pembayaran), TERPISAH dari
 * AdminPanelProvider (panel operasional tiap Yayasan).
 *
 * Kenapa dipisah (bukan cuma disembunyikan di sidebar seperti
 * sebelumnya): supaya platform admin tidak pernah "seolah-olah lagi
 * di dalam 1 Yayasan tertentu" saat mengelola hal yang levelnya
 * platform (harga berlaku untuk SEMUA Yayasan). Satu aplikasi Laravel
 * yang sama, satu database yang sama — cuma 2 "pintu masuk" berbeda.
 *
 * Diakses lewat subdomain terpisah (lihat ->domain() di bawah), diatur
 * via env PLATFORM_DOMAIN — TIDAK pakai APP_URL supaya panel Yayasan
 * (admin biasa) tetap jalan seperti sebelumnya tanpa perubahan apapun.
 * Kalau PLATFORM_DOMAIN belum diisi di .env, panel ini tidak aktif di
 * host manapun (aman, tidak menimpa domain admin panel yang sudah ada).
 */
class PlatformPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('platform')
            ->path('')
            ->domain(config('platform.domain'))
            ->login()
            ->maxContentWidth('full')
            ->brandName('Qinara Platform')
            ->favicon(asset('favicon.ico'))
            ->sidebarCollapsibleOnDesktop()
            // Sama persis dengan AdminPanelProvider — sidebar & topbar putih,
            // supaya Panel Platform terasa satu keluarga visual dengan panel
            // Yayasan, bukan seperti aplikasi lain yang terpisah.
            ->renderHook(
                'panels::head.end',
                fn (): string => '
                    <style>
                        .fi-sidebar{
                            background:white !important;
                            border-right:1px solid #e5e7eb;
                        }

                        .fi-sidebar-nav{
                            background:white !important;
                        }

                        .fi-topbar{
                            background:white !important;
                        }
                    </style>
                '
            )
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Billing')
                    ->icon('heroicon-o-credit-card'),
            ])
            ->colors([
                'primary' => Color::hex('#00A39D'),
            ])
            ->discoverResources(
                in: app_path('Filament/Platform/Resources'),
                for: 'App\\Filament\\Platform\\Resources'
            )
            ->discoverWidgets(
                in: app_path('Filament/Platform/Widgets'),
                for: 'App\\Filament\\Platform\\Widgets'
            )
            ->widgets([
                \App\Filament\Platform\Widgets\PlatformStatsOverview::class,
                \App\Filament\Platform\Widgets\ModulTerlarisWidget::class,
                \App\Filament\Platform\Widgets\YayasanPerluPerhatianWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsurePlatformAdmin::class,
            ]);
    }
}
