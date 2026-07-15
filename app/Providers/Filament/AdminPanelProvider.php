<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Models\Yayasan;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Assets\Css;
use Filament\Navigation\NavigationGroup;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Schema;
use Filament\Facades\Filament;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Ambil Yayasan untuk keperluan branding (dievaluasi per-request,
     * BUKAN sekali di boot).
     *
     * Prioritas: tenant yang sedang aktif di URL (mis. /admin/sdit-tcm/...)
     * — ini penting terutama untuk platform admin, yang bisa berpindah
     * antar yayasan. Fallback ke yayasan milik user (untuk halaman yang
     * belum masuk konteks tenant, mis. halaman pilih tenant).
     */
    protected function resolveYayasanForBranding(): ?Yayasan
    {
        if (! Schema::hasTable('yayasans')) {
            return null;
        }

        $tenant = Filament::getTenant();

        if ($tenant instanceof Yayasan) {
            return $tenant;
        }

        $user = auth()->user();

        if (! $user || empty($user->yayasan_id)) {
            return null;
        }

        return Yayasan::withoutGlobalScopes()->find($user->yayasan_id);
    }

    public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->maxContentWidth('full')
        ->tenant(Yayasan::class, slugAttribute: 'slug')
        ->tenantMiddleware([
            // middleware bawaan Filament sudah otomatis cek canAccessTenant()
            // lewat interface HasTenants di model User — tidak perlu tambahan
            // di sini untuk saat ini.
        ], isPersistent: true)
        ->renderHook(
        'panels::head.end',
        fn (): string => '
            <link rel="stylesheet" href="' . Vite::asset('resources/css/filament/admin/theme.css') . '">

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
        ->brandName(function () {
            $yayasan = $this->resolveYayasanForBranding();

            $logo = $yayasan?->logo
                ? asset('storage/' . $yayasan->logo)
                : null;
        
            $nama = $yayasan?->nama ?? 'Qinara App';
        
            return new HtmlString('
                <div style="display:flex; align-items:center; gap:10px;">
                    ' . ($logo ? '<img src="'.$logo.'" style="height:32px;">' : '') . '
                    <span style="font-weight:600; font-size:16px;">
                        '.$nama.'
                    </span>
                </div>
            ');
        })

        ->favicon(function () {
            $yayasan = $this->resolveYayasanForBranding();

            return $yayasan?->logo
                ? asset('storage/' . $yayasan->logo)
                : asset('favicon.ico');
        })

        ->sidebarCollapsibleOnDesktop()
        
        ->navigationGroups([
            NavigationGroup::make('Master Data')
                ->icon('heroicon-o-folder'),

            NavigationGroup::make('Manajemen Sekolah')
                ->icon('heroicon-o-megaphone'),

            NavigationGroup::make('PSB')
                ->icon('heroicon-o-user-circle'),

            NavigationGroup::make('Keuangan')
                ->icon('heroicon-o-banknotes'),

            NavigationGroup::make('Akademik')
                ->icon('heroicon-o-academic-cap'),

            NavigationGroup::make('Absensi')
                ->icon('heroicon-o-clipboard-document-check'),

            NavigationGroup::make('Tahfidz')
                ->icon('heroicon-o-book-open'),

            NavigationGroup::make('Perizinan')
                ->icon('heroicon-o-document-text'),

            NavigationGroup::make('Konseling')
                ->icon('heroicon-o-chat-bubble-oval-left-ellipsis'),

            NavigationGroup::make('Master Setting')
                ->icon('heroicon-o-cog-6-tooth'),                 
        ])

        ->colors([
            'primary' => Color::hex('#00A39D'),
        ])

        ->discoverResources(
            in: app_path('Filament/Resources'),
            for: 'App\\Filament\\Resources'
        )

        ->discoverPages(
            in: app_path('Filament/Pages'),
            for: 'App\\Filament\\Pages'
        )

        ->discoverWidgets(
            in: app_path('Filament/Widgets'),
            for: 'App\\Filament\\Widgets'
        )

        ->widgets([
            //Widgets\AccountWidget::class,
            //Widgets\FilamentInfoWidget::class,
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
        ->plugins([
            FilamentShieldPlugin::make(),
        ])
        
        ->authMiddleware([
            Authenticate::class,
        ]);
}
}