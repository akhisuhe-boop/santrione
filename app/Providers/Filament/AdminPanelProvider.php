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
            PanelsRenderHook::TOPBAR_START,
            fn (): string => session('impersonator_id')
                ? '<a href="' . route('tenant.impersonate.stop-request') . '"
                       style="display:flex;align-items:center;gap:6px;background:#fef3c7;color:#92400e;padding:6px 14px;font-size:13px;font-weight:600;border-radius:9999px;margin-left:12px;text-decoration:none;">
                       ⚠️ Mode Login-sebagai — Kembali ke Platform
                   </a>'
                : ''
        )
        ->renderHook(
            PanelsRenderHook::CONTENT_START,
            function (): string {
                $tenant = \Filament\Facades\Filament::getTenant();

                if (! $tenant || $tenant->status !== 'trial' || ! $tenant->trial_ends_at) {
                    return '';
                }

                $sisaHari = (int) ceil(($tenant->trial_ends_at->timestamp - now()->timestamp) / 86400);
                $sisaHari = max($sisaHari, 0);

                $urlLangganan = \App\Filament\Pages\Langganan::getUrl(tenant: $tenant);

                return '
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;background:#ecfeff;border:1px solid #a5f3fc;color:#155e75;padding:10px 18px;border-radius:14px;margin-bottom:16px;font-size:13px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-weight:700;">🎁 Masa Trial</span>
                            <span>' . ($sisaHari > 0
                                ? "Sisa {$sisaHari} hari lagi dari 14 hari masa coba."
                                : 'Hari terakhir masa coba Anda.') . '</span>
                        </div>
                        <a href="' . $urlLangganan . '"
                           style="background:#00A39D;color:#fff;padding:6px 16px;border-radius:9999px;font-weight:600;text-decoration:none;white-space:nowrap;">
                           Aktifkan Sekarang
                        </a>
                    </div>
                ';
            }
        )
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

        // PERUBAHAN 7 September 2026: closure ->navigation() custom yang
        // sempat ditambahkan 24 Agustus 2026 (untuk sembunyikan menu
        // lain di sidebar saat Yayasan suspended) DIHAPUS TOTAL --
        // ternyata mematikan auto-generate navigasi bawaan Filament
        // untuk SEMUA kondisi (bukan cuma saat suspended), karena
        // $builder yang di-terima closure itu SELALU kosong dari awal
        // (bukan "builder asli" yang sudah terisi 50 Resource + 18
        // Page seperti yang diasumsikan sebelumnya) -- dikonfirmasi
        // lewat dd() langsung di closure-nya (groups: [], items: [])
        // dan lewat dokumentasi resmi Filament: "When using a custom
        // navigation builder, automatic navigation generation is
        // disabled. You must explicitly include all resources and
        // pages you want to appear." Akibatnya SEMUA Yayasan (termasuk
        // yang baru daftar & yang aksesnya normal) dapat sidebar KOSONG
        // TOTAL -- bug ini baru ketahuan 7 Sep 2026, untungnya belum
        // pernah ter-deploy ke production (cuma ada di branch dev).
        //
        // Sidebar sekarang balik ke auto-generate default Filament
        // (aman, terbukti benar sebelum 24 Agustus). Konsekuensinya:
        // saat Yayasan suspended, sidebar TIDAK LAGI otomatis
        // dipersempit ke "Langganan" saja secara visual -- menu lain
        // (Master Data, Keuangan, dst) tetap kelihatan di sidebar.
        // TAPI ini cuma soal tampilan/UX, BUKAN celah keamanan: klik
        // menu manapun selain Langganan tetap dipaksa redirect balik
        // ke halaman Langganan oleh RedirectSuspendedYayasan (lihat
        // authMiddleware di bawah) -- itu sudah dan tetap jadi
        // satu-satunya sumber kebenaran soal boleh/tidaknya akses,
        // tidak berubah sama sekali oleh perubahan ini.
        //
        // "Sembunyikan sidebar saat suspended" boleh dikerjakan lagi
        // nanti sebagai perbaikan UX terpisah, TAPI harus lewat cara
        // yang tidak mematikan auto-generate default (misal: cek
        // hasAccess() di canAccess()/shouldRegisterNavigation() tiap
        // Resource lewat trait bersama, bukan lewat ->navigation()
        // closure lagi) -- supaya tidak mengulang bug yang sama.

        ->authMiddleware([
            Authenticate::class,
            RedirectSuspendedYayasan::class,
        ]);
}
}
