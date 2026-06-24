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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
{
    $yayasan = Yayasan::first();

    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login()
        ->maxContentWidth('full')
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
        
        ->brandName(
            $yayasan?->nama ?? 'yayasan'
        )

        ->brandName(function () use ($yayasan) {
    $logo = $yayasan?->logo
        ? asset('storage/' . $yayasan->logo)
        : null;

    $nama = $yayasan?->nama ?? 'Yayasan';

    return new HtmlString('
            <div style="display:flex; align-items:center; gap:10px;">
                ' . ($logo ? '<img src="'.$logo.'" style="height:32px;">' : '') . '
                <span style="font-weight:600; font-size:16px;">
                    '.$nama.'
                </span>
            </div>
        ');
    })

        ->favicon(
            $yayasan?->logo
                ? asset('storage/' . $yayasan->logo)
                : asset('favicon.ico')
        )

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

        ->pages([
            Pages\Dashboard::class,
            \App\Filament\Pages\JadwalGrid::class,
        ])

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