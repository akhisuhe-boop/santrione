<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Models\Yayasan;
use App\Http\Middleware\RedirectSuspendedYayasan;
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
     * Prioritas:
     * 1. Platform admin -> selalu null (fallback ke branding "Qinara App"),
     *    terlepas dari tenant mana yang sedang dilihat.
     * 2. Tenant yang sedang aktif di URL (mis. /admin/sdit-tcm/...)
     *    — untuk user tenant biasa.
     * 3. Fallback ke yayasan milik user (untuk halaman yang belum masuk
     *    konteks tenant, mis. halaman pilih tenant).
     * 4. Belum login sama sekali (mis. /admin/login) -- cek session
     *    'active_public_yayasan_id' dari portal publik /y/{slug}, supaya
     *    branding halaman login menyamakan dengan portal wali/guru/ppdb.
     */
    protected function resolveYayasanForBranding(): ?Yayasan
    {
        if (! Schema::hasTable('yayasans')) {
            return null;
        }

        $user = auth()->user();

        // Platform admin selalu tampil branding "Qinara App",
        // terlepas dari tenant mana yang sedang dilihat.
        if ($user && $user->is_platform_admin) {
            return null;
        }

        $tenant = Filament::getTenant();

        if ($tenant instanceof Yayasan) {
            return $tenant;
        }

        if ($user && ! empty($user->yayasan_id)) {
            return Yayasan::withoutGlobalScopes()->find($user->yayasan_id);
        }

        // Belum login (mis. di halaman /admin/login) -- cek apakah user
        // datang lewat pintu masuk /y/{slug}. Ini menyamakan branding
        // halaman login admin dengan portal wali/guru/ppdb.
        $sessionId = session('active_public_yayasan_id');

        if ($sessionId) {
            return Yayasan::withoutGlobalScopes()->find($sessionId);
        }

        return null;
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
                ? \App\Support\FileUrlResolver::public($yayasan->logo)
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
                ? \App\Support\FileUrlResolver::public($yayasan->logo)
                : asset('favicon.ico');
        })

        ->sidebarCollapsibleOnDesktop()

        ->navigationGroups([
            NavigationGroup::make('Platform (SaaS)')
                ->icon('heroicon-o-building-office-2'),

            NavigationGroup::make('Master Data')
                ->icon('heroicon-o-folder'),

            NavigationGroup::make('Manajemen Sekolah')
                ->icon('heroicon-o-megaphone'),

            NavigationGroup::make('PSB')
                ->icon('heroicon-o-user-circle'),

            NavigationGroup::make('Keuangan')
                ->icon('heroicon-o-banknotes'),

            NavigationGroup::make('e-Kantin')
                ->icon('heroicon-o-shopping-bag'),

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
        ->navigationItems([
            \Filament\Navigation\NavigationItem::make('Langganan')
                ->icon('heroicon-o-credit-card')
                ->url('/langganan')
                ->sort(-1)
                ->visible(fn () => auth()->check()
                    && ! auth()->user()->is_platform_admin
                    && auth()->user()->hasRole('Admin Yayasan')),
        ])

        // PERUBAHAN 24 Agustus 2026: begitu Yayasan tidak punya akses
        // (lihat Yayasan::hasAccess()), SEMUA request tetap otomatis
        // di-redirect ke halaman Langganan oleh RedirectSuspendedYayasan
        // -- tapi sebelum patch ini, sidebar-nya masih nampilin semua
        // menu lain (Master Data, Keuangan, dst) yang toh nggak akan
        // pernah kebuka. Override ini bikin sidebar-nya SENDIRI ikut
        // konsisten: cuma menu "Langganan" yang kelihatan, biar tidak
        // membingungkan (bukan cuma soal fungsi, tapi soal tampilan).
        // Platform admin & Yayasan yang masih normal TIDAK terpengaruh
        // sama sekali -- closure ini langsung return builder aslinya.
        ->navigation(function (\Filament\Navigation\NavigationBuilder $builder): \Filament\Navigation\NavigationBuilder {
            $user = auth()->user();
            $yayasan = ($user && ! $user->is_platform_admin) ? $user->yayasan : null;

            // DEBUG SEMENTARA (7 Sep 2026) -- HAPUS setelah selesai
            // ditelusuri. Aktif cuma kalau ditambah ?navdebug=1 di URL,
            // supaya tidak mengganggu user lain sama sekali.
            if (request()->query('navdebug') === '1') {
                dd([
                    'user_email' => $user?->email,
                    'is_platform_admin' => $user?->is_platform_admin,
                    'yayasan_id' => $yayasan?->id,
                    'has_access' => $yayasan?->hasAccess(),
                    'has_role_admin_yayasan' => $user?->hasRole('Admin Yayasan'),
                    'permissions_count' => $user?->getAllPermissions()->count(),
                    'panel_resources_count' => count(\Filament\Facades\Filament::getPanel('admin')->getResources()),
                    'panel_pages_count' => count(\Filament\Facades\Filament::getPanel('admin')->getPages()),
                    'current_tenant' => \Filament\Facades\Filament::getTenant()?->id,
                    'current_tenant_via_yayasan' => \Filament\Facades\Filament::getTenant(),
                    'builder_raw' => $builder,
                ]);
            }

            if ($yayasan && ! $yayasan->hasAccess()) {
                return $builder->items([
                    \Filament\Navigation\NavigationItem::make('Langganan')
                        ->icon('heroicon-o-credit-card')
                        ->url('/langganan')
                        ->isActiveWhen(fn (): bool => true),
                ]);
            }

            return $builder;
        })

        ->authMiddleware([
            Authenticate::class,
            RedirectSuspendedYayasan::class,
        ]);
}
}
