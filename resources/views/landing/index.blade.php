<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting->brand_name }} - Aplikasi Manajemen & Digitalisasi Lembaga Pendidikan Islam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#E6F6F5', 100: '#CCECEB', 200: '#99DAD7', 300: '#66C7C3', 400: '#33B5B0',
                            500: '#00A39D', 600: '#00938E', 700: '#00736F', 800: '#00524F', 900: '#00312F',
                        },
                        slate: { 900: '#0F172A' }
                    },
                    boxShadow: {
                        'premium': '0 10px 30px -10px rgba(0, 163, 157, 0.05)',
                        'premium-hover': '0 20px 40px -15px rgba(0, 163, 157, 0.12)',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; color: #0F172A; }
        .gradient-bg {
            background: radial-gradient(circle at 10% 20%, rgba(0, 163, 157, 0.05) 0%, transparent 50%),
                        radial-gradient(circle at 90% 80%, rgba(0, 163, 157, 0.03) 0%, transparent 50%);
        }
        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(-100%); }
        }
        .animate-marquee { animation: marquee 30s linear infinite; }
        .animate-marquee:hover { animation-play-state: paused; }
        .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .reveal-on-scroll { opacity: 0; transition: opacity 0.7s ease-out; }
        .reveal-on-scroll.revealed { opacity: 1; }
        .reveal-on-scroll:nth-child(1) { transition-delay: 0ms; }
        .reveal-on-scroll:nth-child(2) { transition-delay: 120ms; }
        .reveal-on-scroll:nth-child(3) { transition-delay: 240ms; }
    </style>

    {{-- Meta (Facebook) Pixel --}}
    @if($setting->meta_pixel_id)
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $setting->meta_pixel_id }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none" alt=""
        src="https://www.facebook.com/tr?id={{ $setting->meta_pixel_id }}&ev=PageView&noscript=1"/></noscript>
    @endif

    {{-- TikTok Pixel --}}
    @if($setting->tiktok_pixel_id)
    <script>
        !function (w, d, t) {
            w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=i+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};
            ttq.load('{{ $setting->tiktok_pixel_id }}');
            ttq.page();
        }(window, document, 'ttq');
    </script>
    @endif

    {{-- Google Ads / Google Tag --}}
    @if($setting->google_ads_id)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $setting->google_ads_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $setting->google_ads_id }}');
    </script>
    @endif
</head>
<body class="gradient-bg antialiased">

@php
    $brandParts = explode(' ', $setting->brand_name ?: 'Qinara Apps', 2);
    $logoUrl = $setting->logo ? \Illuminate\Support\Facades\Storage::disk('r2-public')->url($setting->logo) : null;
@endphp

<a href="javascript:void(0)" onclick="hubungiSales()"
   style="position: fixed; right: 24px; bottom: 24px; width: 64px; height: 64px; border-radius: 9999px;
          background: #25D366; display: flex; align-items: center; justify-content: center; z-index: 999999;
          box-shadow: 0 10px 25px rgba(0,0,0,.25); cursor: pointer;">
    <i class="fa-brands fa-whatsapp" style="font-size:34px;color:#fff;"></i>
</a>

@if($buktiSosialList->isNotEmpty())
<div id="social-proof-popup"
     class="fixed left-4 bottom-4 md:left-6 md:bottom-6 z-[999998] max-w-[280px] bg-white rounded-2xl shadow-2xl border border-slate-100 p-4 flex items-center gap-3 opacity-0 translate-y-3 pointer-events-none transition-all duration-500 ease-out">
    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center shrink-0">
        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
    </div>
    <div class="min-w-0 flex-1">
        <p id="social-proof-name" class="text-sm font-bold text-slate-900 truncate"></p>
        <p class="text-xs text-slate-500">menggunakan {{ $setting->brand_name }}<span id="social-proof-time"></span></p>
    </div>
    <button onclick="dismissSocialProof()" aria-label="Tutup" class="text-slate-300 hover:text-slate-500 shrink-0">
        <i data-lucide="x" class="w-4 h-4"></i>
    </button>
</div>
@endif

<!-- HEADER -->
<header class="fixed inset-x-0 top-0 z-40 w-full border-b border-slate-100 bg-white/70 backdrop-blur-xl">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 lg:px-8">

        @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="{{ $setting->brand_name }}" class="h-9 w-auto">
        @else
        <div class="flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-500 text-white shadow-sm">
                <i data-lucide="graduation-cap" class="h-6 w-6"></i>
            </div>
            <span class="text-xl font-bold tracking-tight text-slate-900">
                {{ $brandParts[0] }}<span class="text-primary-500">{{ $brandParts[1] ?? '' }}</span>
            </span>
        </div>
        @endif

        <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
            <a href="#masalah" class="hover:text-primary-500 transition-colors">Masalah</a>
            <a href="#solusi" class="hover:text-primary-500 transition-colors">Solusi</a>
            <a href="#fitur" class="hover:text-primary-500 transition-colors">Fitur Unggulan</a>
            @if($mockupScreenshots->isNotEmpty())
            <a href="#tampilan-aplikasi" class="hover:text-primary-500 transition-colors">Tampilan Aplikasi</a>
            @endif
            <a href="#testimoni" class="hover:text-primary-500 transition-colors">Testimoni</a>
            <a href="#harga" class="hover:text-primary-500 transition-colors">Harga</a>
            <a href="#faq" class="hover:text-primary-500 transition-colors">FAQ</a>
        </nav>

        <div class="hidden md:flex items-center gap-4">
            <a href="{{ route('public.daftar') }}"
                class="rounded-full bg-primary-500 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-primary-600 hover:scale-[1.02] active:scale-[0.98] transition-all">
                Coba Demo Gratis
            </a>
        </div>

        <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-600">
            <i id="menu-icon" data-lucide="menu" class="h-6 w-6"></i>
        </button>
    </div>

    <div id="mobile-menu" class="hidden md:hidden border-t border-slate-100 bg-white px-4 py-4 space-y-3 shadow-lg">
        <a href="#masalah" class="block py-2 text-sm font-medium text-slate-700 hover:text-primary-500">Masalah</a>
        <a href="#solusi" class="block py-2 text-sm font-medium text-slate-700 hover:text-primary-500">Solusi</a>
        <a href="#fitur" class="block py-2 text-sm font-medium text-slate-700 hover:text-primary-500">Fitur Unggulan</a>
        @if($mockupScreenshots->isNotEmpty())
        <a href="#tampilan-aplikasi" class="block py-2 text-sm font-medium text-slate-700 hover:text-primary-500">Tampilan Aplikasi</a>
        @endif
        <a href="#testimoni" class="block py-2 text-sm font-medium text-slate-700 hover:text-primary-500">Testimoni</a>
        <a href="#harga" class="block py-2 text-sm font-medium text-slate-700 hover:text-primary-500">Harga</a>
        <a href="#faq" class="block py-2 text-sm font-medium text-slate-700 hover:text-primary-500">FAQ</a>
        <a href="{{ route('public.daftar') }}" class="block text-center w-full rounded-full bg-primary-500 py-3 text-sm font-semibold text-white shadow-md">
            Coba Demo Gratis
        </a>
    </div>
</header>

<!-- HERO -->
<section id="hero" class="relative pt-28 pb-24 md:pt-36 md:pb-32 overflow-hidden bg-gradient-to-b from-white via-white to-slate-50">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-14 items-center">

            <div class="lg:col-span-7 text-center lg:text-left space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700 mx-auto lg:mx-0 shadow-sm hover:shadow-md transition-all duration-300">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full rounded-full opacity-60 animate-ping" style="background-color:#00A39D;"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full" style="background-color:#00A39D;"></span>
                    </span>
                    {{ $setting->badge_text ?? 'Sistem Manajemen Lembaga Pendidikan Islam Modern' }}
                </div>

                <h1 class="mt-6 mb-4 text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-slate-900 leading-tight sm:leading-[1.1]">
                    {{ $setting->headline_baris1 }}
                    <br class="hidden sm:block">
                    <span class="block mt-2 text-xl sm:text-2xl md:text-3xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-primary-700">
                        {{ $setting->headline_baris2 }}
                    </span>
                </h1>

                <p class="text-base sm:text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                    {{ $setting->subheadline }}
                </p>

                <div class="flex flex-wrap justify-center lg:justify-start gap-3">
                    <span class="flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 rounded-full text-xs font-semibold border border-red-100">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> Data Berantakan
                    </span>
                    <span class="flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 rounded-full text-xs font-semibold border border-red-100">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> Keuangan Tidak Transparan
                    </span>
                    <span class="flex items-center gap-2 px-3 py-1.5 bg-red-50 text-red-600 rounded-full text-xs font-semibold border border-red-100">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> Proses Manual Lambat
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('public.daftar') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-primary-600 px-8 py-4 text-white font-bold shadow-lg hover:bg-primary-700 hover:scale-[1.02] transition">
                        Coba Demo Gratis
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                    <button onclick="hubungiSales()"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-white border border-slate-200 px-8 py-4 font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                        <i data-lucide="message-circle" class="w-5 h-5 text-emerald-500"></i>
                        Konsultasi Gratis
                    </button>
                </div>

                <div class="flex items-center justify-center lg:justify-start gap-4 pt-2">
                    <div class="flex -space-x-2">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-slate-200 to-slate-300 border-2 border-white flex items-center justify-center shadow-sm">
                            <span class="text-xs font-bold text-slate-700">AH</span>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 border-2 border-white flex items-center justify-center shadow-sm">
                            <span class="text-xs font-bold text-primary-700">MZ</span>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-100 to-amber-200 border-2 border-white flex items-center justify-center shadow-sm">
                            <span class="text-xs font-bold text-amber-700">FS</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-500">
                        Dipakai oleh <span class="font-bold text-slate-800">{{ $setting->social_proof_text ?? '120+ Lembaga Pendidikan Islam' }}</span> di Indonesia
                    </p>
                </div>
            </div>

            <div class="lg:col-span-5">
                @if($setting->hero_mockup_gambar)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('r2-public')->url($setting->hero_mockup_gambar) }}"
                         alt="Tampilan Dashboard {{ $setting->brand_name }}"
                         class="w-full rounded-2xl border border-slate-200 shadow-2xl">
                @else
                <div class="relative rounded-2xl bg-white border border-slate-200 shadow-2xl p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6">
                        <div class="flex gap-1.5">
                            <span class="w-3 h-3 rounded-full bg-red-400"></span>
                            <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                            <span class="w-3 h-3 rounded-full bg-green-400"></span>
                        </div>
                        <div class="text-xs text-slate-400 font-mono">app.qinaraindonesia.id</div>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <p class="text-xs text-slate-400 font-semibold">Total Keuangan</p>
                            <h3 class="text-2xl font-bold text-slate-900">{{ $setting->hero_kpi_keuangan ?? 'Rp 142.850.000' }}</h3>
                            <p class="text-xs text-green-600 font-semibold mt-1">{{ $setting->hero_kpi_keuangan_growth ?? '+12% bulan ini' }}</p>
                        </div>
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-slate-500">Kehadiran Siswa</span>
                                <span class="font-bold text-primary-600">{{ $setting->hero_kpi_kehadiran_persen ?? 98.2 }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full">
                                <div class="bg-primary-500 h-2 rounded-full" style="width:{{ $setting->hero_kpi_kehadiran_persen ?? 98.2 }}%"></div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border">
                                <div>
                                    <p class="text-sm font-semibold">Ahmad Muzzaki</p>
                                    <p class="text-xs text-slate-400">Hafalan Juz 30</p>
                                </div>
                                <span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded">Lancar</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border">
                                <div>
                                    <p class="text-sm font-semibold">Zahra Nurfadhilah</p>
                                    <p class="text-xs text-slate-400">Izin Sakit</p>
                                </div>
                                <span class="text-xs bg-yellow-100 text-yellow-600 px-2 py-1 rounded">Izin</span>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -right-4 bg-slate-900 text-white p-3 rounded-xl shadow-xl flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-5 h-5 text-primary-400"></i>
                        <div>
                            <p class="text-[10px] text-slate-400">Security</p>
                            <p class="text-xs font-bold">Encrypted</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>
</section>

<!-- BEFORE / AFTER - dinamis -->
<section id="masalah" class="py-24 bg-gradient-to-b from-white to-slate-50">
<div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="max-w-3xl mx-auto text-center">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary-50 text-primary-600 text-sm font-semibold">
            <i data-lucide="sparkles" class="w-4 h-4"></i> Transformasi Digital Lembaga Pendidikan Islam
        </span>
        <h2 class="mt-6 text-3xl font-extrabold text-slate-900 sm:text-4xl tracking-tight leading-tight">
            Tinggalkan Sistem Manual,<br>
            <span class="text-primary-600">Masuk ke Era Digital Terintegrasi</span>
        </h2>
        <p class="mt-5 text-lg text-slate-600">
            Semua operasional lembaga—keuangan, akademik, tahfidz, absensi, hingga laporan—
            dalam satu sistem yang cepat, rapi, dan real-time.
        </p>
    </div>

    @if($masalahSolusi->isNotEmpty())
    <div class="mt-16 relative grid lg:grid-cols-2 gap-8 items-stretch">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm hover:shadow-md transition">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-red-50 flex items-center justify-center">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-500"></i>
                </div>
                <div>
                    <h3 class="font-bold text-xl text-slate-900">Sistem Manual</h3>
                    <p class="text-slate-500 text-sm">Sebelum {{ $setting->brand_name }}</p>
                </div>
            </div>
            <div class="space-y-4 text-slate-600">
                @foreach($masalahSolusi as $ms)
                <div class="flex items-start gap-2">
                    <i data-lucide="x-circle" class="w-5 h-5 text-red-400 mt-0.5"></i> {{ $ms->teks_masalah }}
                </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-3xl bg-gradient-to-br from-[#00A39D] to-[#008C87] text-white p-8 shadow-xl hover:shadow-2xl transition">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="font-bold text-xl">Digital Terintegrasi</h3>
                    <p class="text-white/80 text-sm">Dengan {{ $setting->brand_name }}</p>
                </div>
            </div>
            <div class="space-y-4 text-white/90">
                @foreach($masalahSolusi as $ms)
                <div class="flex items-start gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-white/90 mt-0.5"></i> {{ $ms->teks_solusi }}
                </div>
                @endforeach
            </div>
        </div>

        <div class="hidden lg:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
            <div class="w-14 h-14 rounded-full bg-white shadow-lg border border-slate-200 flex items-center justify-center">
                <i data-lucide="arrow-right" class="w-7 h-7 text-primary-600"></i>
            </div>
        </div>
    </div>
    @endif

    <div class="mt-14 grid grid-cols-2 md:grid-cols-4 gap-6">
        <div class="text-center">
            <div class="text-3xl font-bold text-primary-600">{{ $setting->stat_efisiensi ?? '70%' }}</div>
            <p class="text-sm text-slate-500 mt-1">Lebih Efisien</p>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-primary-600">{{ $setting->stat_modul ?? '10+' }}</div>
            <p class="text-sm text-slate-500 mt-1">Modul Terintegrasi</p>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-primary-600">{{ $setting->stat_akses ?? '24/7' }}</div>
            <p class="text-sm text-slate-500 mt-1">Akses Real-time</p>
        </div>
        <div class="text-center">
            <div class="text-3xl font-bold text-primary-600">{{ $setting->stat_digitalisasi ?? '100%' }}</div>
            <p class="text-sm text-slate-500 mt-1">Digitalisasi</p>
        </div>
    </div>
</div>
</section>

<!-- VIDEO DEMO - besar, center, dinamis -->
@if($setting->hero_video_url)
<section class="py-16 md:py-20 bg-white border-t border-slate-100">
    <div class="mx-auto max-w-4xl px-4 lg:px-8 text-center">
        <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3.5 py-1.5 rounded-full border border-primary-100">Lihat Cara Kerjanya</span>
        <h2 class="mt-4 text-3xl font-extrabold text-slate-900 sm:text-4xl">Kenali {{ $setting->brand_name }} Lebih Dekat</h2>
        @if($setting->video_deskripsi)
        <p class="mt-4 text-base text-slate-600 max-w-2xl mx-auto">{{ $setting->video_deskripsi }}</p>
        @endif
        <div class="mt-6 rounded-2xl overflow-hidden border border-slate-200 shadow-2xl aspect-video bg-slate-900">
            @if($setting->heroVideoIsEmbed())
                <iframe src="{{ $setting->heroVideoEmbedUrl() }}" class="w-full h-full" title="Video {{ $setting->brand_name }}" frameborder="0" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
            @else
                <video class="w-full h-full object-cover" src="{{ $setting->hero_video_url }}" autoplay muted loop playsinline controls></video>
            @endif
        </div>
    </div>
</section>
@endif

<!-- SOLUSI EKOSISTEM - dinamis -->
@if($ekosistemSolusi->isNotEmpty())
<section id="solusi" class="py-20 md:py-28 bg-[#FFFFFF]">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3 py-1 rounded-full">Solusi Terpadu</span>
            <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl tracking-tight">
                Satu Platform, Solusi untuk Semua Kebutuhan Lembaga Pendidikan Islam
            </h2>
            <p class="text-base text-slate-600 leading-relaxed">
                {{ $setting->brand_name }} membagi platform menjadi ekosistem khusus guna mempermudah koordinasi antar pemangku kepentingan di lingkungan lembaga Anda.
            </p>
        </div>

        <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach($ekosistemSolusi as $s)
            <div class="bg-[#F8FAFC] rounded-3xl p-8 border border-slate-100 shadow-premium flex flex-col justify-between hover:border-primary-400 transition-all duration-300 group">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary-50 text-primary-500 flex items-center justify-center group-hover:bg-primary-500 group-hover:text-white transition-all duration-300">
                        <i data-lucide="{{ $s->icon }}" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">{{ $s->judul }}</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">{{ $s->deskripsi }}</p>
                </div>
                @if($s->tag_text)
                <div class="pt-6 border-t border-slate-200/60 mt-6">
                    <span class="text-xs font-bold text-primary-500 flex items-center gap-1 group-hover:gap-2 transition-all">
                        {{ $s->tag_text }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- MODUL APLIKASI - dinamis -->
@if($modulAplikasi->isNotEmpty())
<section id="fitur" class="py-20 md:py-28 bg-[#F8FAFC] overflow-hidden">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
            <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3.5 py-1.5 rounded-full border border-primary-100">Modul Aplikasi</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Infrastruktur Digital Terlengkap dalam Ekosistem Premium
            </h2>
            <p class="text-base text-slate-600 max-w-2xl mx-auto">
                Kelola lembaga pendidikan Islam Anda dengan standar teknologi SaaS modern global. Tampilan bersih, responsif, stabil, dan dilengkapi sistem visualisasi proteksi data.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
            @foreach($modulAplikasi->where('is_featured', false) as $m)
            <div class="group relative rounded-2xl bg-white border border-slate-200/60 p-7 shadow-[0_1px_3px_rgba(0,0,0,0.04)] hover:shadow-xl hover:-translate-y-1 hover:border-primary-300 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                <div class="absolute -right-8 -top-8 w-28 h-28 bg-primary-500/[0.04] rounded-full blur-2xl group-hover:bg-primary-500/[0.08] transition-colors duration-300"></div>
                <div class="relative space-y-4">
                    <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 text-primary-600 ring-1 ring-primary-100 group-hover:from-primary-500 group-hover:to-primary-600 group-hover:text-white group-hover:ring-primary-500 transition-all duration-300">
                        <i data-lucide="{{ $m->icon }}" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-primary-600 transition-colors">{{ $m->judul }}</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">{{ $m->deskripsi }}</p>
                </div>
                @if($m->tag_text)
                <div class="relative mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-400">{{ $m->tag_text }}</span>
                    <i data-lucide="arrow-up-right" class="w-4 h-4 text-slate-300 group-hover:text-primary-500 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
                </div>
                @endif
            </div>
            @endforeach

            @foreach($modulAplikasi->where('is_featured', true) as $m)
            <div class="md:col-span-2 lg:col-span-3 group relative rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 p-8 md:p-10 shadow-xl overflow-hidden">
                <div class="absolute inset-0 opacity-[0.07] bg-[radial-gradient(#00A39D_1px,transparent_1px)] [background-size:18px_18px]"></div>
                <div class="absolute -right-16 -bottom-16 w-72 h-72 bg-primary-500/10 rounded-full blur-3xl"></div>
                <div class="relative z-10 flex flex-col lg:flex-row gap-8 justify-between items-center">
                    <div class="space-y-4 max-w-xl">
                        <div class="w-12 h-12 flex items-center justify-center rounded-xl bg-primary-500/20 text-primary-300 ring-1 ring-primary-400/30">
                            <i data-lucide="{{ $m->icon }}" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white">{{ $m->judul }}</h3>
                        <p class="text-sm text-slate-300 leading-relaxed">{{ $m->deskripsi }}</p>
                    </div>
                    <div class="shrink-0 w-full lg:w-auto">
                        <button onclick="hubungiSales()"
                            class="w-full lg:w-auto inline-flex items-center justify-center gap-2 text-center rounded-full bg-primary-500 text-white hover:bg-primary-400 hover:scale-105 transition-all duration-300 text-sm font-semibold py-3 px-7 shadow-md">
                            Hubungi via WhatsApp
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- GALERI SCREENSHOT ASLI - dinamis -->
@if($mockupScreenshots->isNotEmpty())
<section id="tampilan-aplikasi" class="py-20 bg-white border-t border-slate-100">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
            <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3 py-1 rounded-full">Lihat Langsung</span>
            <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl tracking-tight">Tampilan Asli Aplikasi {{ $setting->brand_name }}</h2>
            <p class="text-base text-slate-600">Bukan mockup rekaan — ini tampilan nyata dari dashboard yang dipakai lembaga mitra kami sehari-hari.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($mockupScreenshots as $m)
            <div class="rounded-2xl overflow-hidden border border-slate-200 shadow-premium bg-white group">
                <div class="overflow-hidden cursor-zoom-in" onclick="openLightbox('{{ $m->url() }}', '{{ addslashes($m->judul) }}')">
                    <img src="{{ $m->url() }}" alt="{{ $m->judul }}" class="w-full h-56 object-cover transition-transform duration-500 group-hover:scale-110">
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-slate-900">{{ $m->judul }}</h3>
                    @if($m->deskripsi)
                    <p class="text-sm text-slate-500 mt-1">{{ $m->deskripsi }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- LIGHTBOX - zoom gambar tampilan aplikasi -->
<div id="image-lightbox" class="fixed inset-0 z-[999999] bg-black/90 hidden items-center justify-center p-4 md:p-10 cursor-zoom-out" onclick="closeLightbox()">
    <button onclick="closeLightbox()" aria-label="Tutup" class="absolute top-5 right-5 text-white/70 hover:text-white transition-colors">
        <i data-lucide="x" class="w-8 h-8"></i>
    </button>
    <figure class="max-w-full max-h-full flex flex-col items-center gap-3">
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[80vh] rounded-xl shadow-2xl cursor-default" onclick="event.stopPropagation()">
        <figcaption id="lightbox-caption" class="text-white/80 text-sm"></figcaption>
    </figure>
</div>
@endif

<!-- STUDI KASUS - dinamis, manual -->
@if($studiKasusList->isNotEmpty())
<section id="studi-kasus" class="py-20 bg-white">
    <div class="mx-auto max-w-7xl px-4 lg:px-8 space-y-16">
        @foreach($studiKasusList as $sk)
        <div class="rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 text-white p-8 md:p-12 shadow-2xl overflow-hidden relative">
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#00A39D_1px,transparent_1px)] [background-size:16px_16px]"></div>
            <div class="relative grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                <div class="lg:col-span-7 space-y-5">
                    @if($sk->badge_text)
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-primary-500/20 border border-primary-400/30 text-primary-300 text-xs font-semibold">
                        <i data-lucide="badge-check" class="w-4 h-4"></i> {{ $sk->badge_text }}
                    </span>
                    @endif
                    <h3 class="text-2xl md:text-3xl font-extrabold">{{ $sk->nama_lembaga }}</h3>
                    @if($sk->deskripsi)
                    <p class="text-slate-300 leading-relaxed">{{ $sk->deskripsi }}</p>
                    @endif
                    @if($sk->kutipan)
                    <blockquote class="border-l-2 border-primary-400 pl-4 italic text-slate-200">
                        "{{ $sk->kutipan }}"
                        @if($sk->kutipan_nama)
                        <footer class="mt-2 not-italic text-sm text-slate-400">
                            — {{ $sk->kutipan_nama }}{{ $sk->kutipan_jabatan ? ', '.$sk->kutipan_jabatan : '' }}
                        </footer>
                        @endif
                    </blockquote>
                    @endif
                </div>
                <div class="lg:col-span-5">
                    @if($sk->fotoUrl())
                    <img src="{{ $sk->fotoUrl() }}" alt="{{ $sk->nama_lembaga }}" class="w-full rounded-2xl mb-6 object-cover max-h-64">
                    @endif
                    @if(!empty($sk->stats))
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($sk->stats as $stat)
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 text-center">
                            <p class="text-2xl font-extrabold text-primary-400">{{ $stat['value'] ?? '' }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ $stat['label'] ?? '' }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- TESTIMONI - dinamis -->
<section id="testimoni" class="py-20 bg-slate-50 border-t border-slate-100">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3.5 py-1.5 rounded-full border border-primary-100">Testimonial</span>
            <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">Kata Mereka Yang Telah Bertransformasi</h2>
            <p class="text-base text-slate-600">
                Kami bangga menjadi bagian dari perkembangan digitalisasi puluhan lembaga pendidikan Islam modern di seluruh penjuru Indonesia.
            </p>
        </div>

        @if($testimonis->isNotEmpty())
        <div class="mt-16 relative max-w-6xl mx-auto">
            <div id="testimoni-scroll" class="no-scrollbar flex gap-6 overflow-x-auto snap-x snap-mandatory scroll-smooth py-8 -my-8 px-1">
                @foreach($testimonis as $t)
                <div class="snap-center shrink-0 w-[88%] sm:w-[70%] md:w-[calc(50%-0.75rem)]">
                    <div class="h-full bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow duration-300 flex flex-col overflow-hidden">
                        <div class="p-8 flex-1 flex flex-col">
                            <div class="flex items-center justify-between mb-5">
                                <div class="flex text-amber-400 gap-0.5">
                                    @for($i = 0; $i < $t->rating; $i++)
                                        <i data-lucide="star" class="fill-current w-4 h-4"></i>
                                    @endfor
                                </div>
                                <i data-lucide="quote" class="w-7 h-7 text-primary-100"></i>
                            </div>
                            <p class="text-[15px] text-slate-700 leading-relaxed flex-1">{{ $t->isi }}</p>
                        </div>
                        <div class="bg-primary-50/70 px-8 py-5 flex items-center gap-4">
                            @if($t->logoUrl())
                                <img src="{{ $t->logoUrl() }}" alt="{{ $t->nama }}" class="w-11 h-11 rounded-full object-cover ring-2 ring-white">
                            @else
                                <div class="w-11 h-11 rounded-full bg-primary-100 flex items-center justify-center font-bold text-sm text-primary-700 ring-2 ring-white">
                                    {{ $t->inisial() }}
                                </div>
                            @endif
                            <div>
                                <h4 class="text-sm font-bold text-slate-900">{{ $t->nama }}</h4>
                                <p class="text-xs text-primary-700/70">{{ $t->jabatan }}{{ $t->asal_pesantren ? ' - '.$t->asal_pesantren : '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($testimonis->count() > 1)
            <button id="testimoni-prev" aria-label="Sebelumnya" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-3 md:-translate-x-14 w-10 h-10 rounded-full bg-white shadow-lg border border-slate-100 flex items-center justify-center hover:bg-primary-500 hover:text-white transition-colors">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </button>
            <button id="testimoni-next" aria-label="Berikutnya" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-3 md:translate-x-14 w-10 h-10 rounded-full bg-white shadow-lg border border-slate-100 flex items-center justify-center hover:bg-primary-500 hover:text-white transition-colors">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
            <div id="testimoni-dots" class="flex justify-center gap-2 mt-4">
                @foreach($testimonis as $i => $t)
                <button data-index="{{ $i }}" class="testimoni-dot h-2 rounded-full transition-all {{ $i === 0 ? 'w-6 bg-primary-500' : 'w-2 bg-slate-300' }}"></button>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        @php $testimoniLogos = $testimonis->filter(fn($t) => $t->logoUrl())->values(); @endphp
        @if($testimoniLogos->isNotEmpty())
        <div class="mt-16">
            <p class="text-center text-xs font-semibold text-slate-400 uppercase tracking-wider mb-8">Dipercaya oleh lembaga-lembaga berikut</p>
            <div class="relative overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_10%,black_90%,transparent)]">
                <div class="flex w-max">
                    <div class="flex gap-16 items-center shrink-0 animate-marquee pr-16">
                        @foreach($testimoniLogos as $t)
                        <img src="{{ $t->logoUrl() }}" alt="{{ $t->nama }}" class="h-20 w-auto grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                        @endforeach
                    </div>
                    <div class="flex gap-16 items-center shrink-0 animate-marquee pr-16" aria-hidden="true">
                        @foreach($testimoniLogos as $t)
                        <img src="{{ $t->logoUrl() }}" alt="" class="h-20 w-auto grayscale opacity-60 hover:grayscale-0 hover:opacity-100 transition-all duration-300">
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="mt-16 bg-white border border-slate-100 rounded-3xl p-8 md:p-10 shadow-premium max-w-4xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center divide-y md:divide-y-0 md:divide-x divide-slate-100 text-center md:text-left">
                <div class="space-y-2 md:pr-4">
                    <div class="flex items-center gap-2 justify-center md:justify-start text-primary-500">
                        <i data-lucide="lock" class="w-5 h-5"></i>
                        <h4 class="font-bold text-slate-900">Sistem Keamanan</h4>
                    </div>
                    <p class="text-xs text-slate-500">Enkripsi database SSL 256-bit standar perbankan untuk proteksi identitas siswa & keuangan.</p>
                </div>
                <div class="space-y-2 pt-4 md:pt-0 md:px-6">
                    <div class="flex items-center gap-2 justify-center md:justify-start text-primary-500">
                        <i data-lucide="cloud-lightning" class="w-5 h-5"></i>
                        <h4 class="font-bold text-slate-900">Cloud Server 99.9% Uptime</h4>
                    </div>
                    <p class="text-xs text-slate-500">Kecepatan akses data tinggi tanpa khawatir server down atau mati listrik di lingkungan lembaga.</p>
                </div>
                <div class="space-y-2 pt-4 md:pt-0 md:pl-6">
                    <div class="flex items-center gap-2 justify-center md:justify-start text-primary-500">
                        <i data-lucide="users" class="w-5 h-5"></i>
                        <h4 class="font-bold text-slate-900">Multi-Level Hak Akses</h4>
                    </div>
                    <p class="text-xs text-slate-500">Pembagian izin akses ketat untuk pimpinan, guru, tata usaha, hingga wali siswa.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HARGA - 3 kartu menyamping, Paket Full di tengah -->
<section id="harga" class="py-20 md:py-28 relative">
    <div class="mx-auto max-w-7xl px-4 lg:px-8 relative">
        <div class="text-center max-w-3xl mx-auto space-y-4">
            <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3.5 py-1.5 rounded-full border border-primary-100">Pilihan Investasi</span>
            <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">Harga Transparan, Tumbuh Sesuai Kebutuhan</h2>
            <p class="text-base text-slate-600">
                Mulai dari biaya dasar yang ringan, lalu tambahkan modul satu per satu hanya untuk yang benar-benar dipakai lembaga Anda.
            </p>
        </div>

        @php
            // Paket "Semua Modul Termasuk" DIPAKSA selalu di posisi tengah,
            // apa pun jumlah paket lain yang aktif -- supaya urutan tampil
            // tidak tergantung urutan data di database.
            $featuredPlan = $subscriptionPlans->firstWhere('termasuk_semua_modul', true);
            $otherPlans = $subscriptionPlans->where('termasuk_semua_modul', false)->values();

            if ($featuredPlan) {
                $half = (int) ceil($otherPlans->count() / 2);
                $orderedPlans = $otherPlans->slice(0, $half)->values()
                    ->push($featuredPlan)
                    ->concat($otherPlans->slice($half)->values());
            } else {
                $orderedPlans = $otherPlans;
            }

            $planCount = max(1, $orderedPlans->count());
            $gridColsClass = match (true) {
                $planCount >= 3 => 'md:grid-cols-3',
                $planCount === 2 => 'md:grid-cols-2',
                default => 'md:grid-cols-1',
            };
        @endphp

        <div class="mt-20 pt-6 grid grid-cols-1 {{ $gridColsClass }} gap-8 max-w-6xl mx-auto items-stretch">
            @foreach($orderedPlans as $plan)
            <div class="reveal-on-scroll relative {{ $plan->termasuk_semua_modul ? 'md:-translate-y-2 bg-gradient-to-b from-slate-900 to-slate-950 text-white rounded-3xl shadow-2xl ring-2 ring-primary-500' : 'bg-white rounded-2xl border border-slate-200 shadow-[0_1px_3px_rgba(0,0,0,0.04)]' }} p-8 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">

                @if($plan->termasuk_semua_modul)
                <div class="absolute -top-5 inset-x-0 flex justify-center z-10">
                    <div class="flex items-center gap-2 bg-gradient-to-r from-primary-400 to-primary-600 text-white text-sm font-bold py-2 px-5 rounded-full shadow-lg uppercase tracking-wider whitespace-nowrap">
                        <i data-lucide="sparkles" class="w-4 h-4"></i> Paling Populer
                    </div>
                </div>
                @endif

                <div>
                    <div class="w-11 h-11 flex items-center justify-center rounded-xl mb-4 {{ $plan->termasuk_semua_modul ? 'bg-primary-500/20 text-primary-300 ring-1 ring-primary-400/30' : 'bg-primary-50 text-primary-600 ring-1 ring-primary-100' }}">
                        <i data-lucide="{{ $plan->termasuk_semua_modul ? 'crown' : 'zap' }}" class="w-5 h-5"></i>
                    </div>

                    <h3 class="text-2xl font-bold {{ $plan->termasuk_semua_modul ? 'text-white' : 'text-slate-900' }}">{{ $plan->nama }}</h3>
                    <p class="text-sm {{ $plan->termasuk_semua_modul ? 'text-slate-300' : 'text-slate-600' }} mt-2 leading-relaxed">{{ $plan->deskripsi }}</p>

                    <div class="mt-6 flex items-baseline">
                        <span class="text-4xl font-extrabold tracking-tight {{ $plan->termasuk_semua_modul ? 'text-white' : 'text-slate-900' }}">Rp {{ number_format($plan->harga_bulanan, 0, ',', '.') }}</span>
                        <span class="text-xs {{ $plan->termasuk_semua_modul ? 'text-slate-400' : 'text-slate-500' }} ml-1">/ bulan</span>
                    </div>
                    <p class="text-xs {{ $plan->termasuk_semua_modul ? 'text-slate-400' : 'text-slate-500' }} mt-2">
                        Termasuk {{ $plan->maks_siswa ?? 'tanpa batas' }} siswa
                        @if($plan->maks_lembaga) & {{ $plan->maks_lembaga }} lembaga @endif.
                        @if($plan->harga_per_siswa_tambahan)
                            Siswa tambahan +Rp{{ number_format($plan->harga_per_siswa_tambahan, 0, ',', '.') }}/siswa.
                        @endif
                    </p>

                    <div class="mt-6 h-px w-full {{ $plan->termasuk_semua_modul ? 'bg-white/10' : 'bg-slate-100' }}"></div>

                    <ul class="mt-6 space-y-3.5 text-sm {{ $plan->termasuk_semua_modul ? 'text-slate-300' : 'text-slate-600' }}">
                        @if($plan->termasuk_semua_modul)
                        <li class="flex items-center gap-2.5">
                            <span class="flex items-center justify-center w-5 h-5 rounded-full bg-primary-500/20 shrink-0">
                                <i data-lucide="check" class="w-3 h-3 text-primary-300"></i>
                            </span>
                            <span class="font-medium text-white">Semua modul aplikasi tanpa kecuali</span>
                        </li>
                        @else
                            @foreach($plan->fitur ?? [] as $fiturKey)
                            <li class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-primary-50 shrink-0">
                                    <i data-lucide="check" class="w-3 h-3 text-primary-600"></i>
                                </span>
                                <span>{{ \App\Support\FeatureGate::label($fiturKey) }}</span>
                            </li>
                            @endforeach
                            <li class="flex items-center gap-2.5 text-slate-400">
                                <span class="flex items-center justify-center w-5 h-5 rounded-full bg-slate-50 shrink-0">
                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                </span>
                                <span>Tambah modul lain sesuai kebutuhan</span>
                            </li>
                        @endif
                    </ul>
                </div>

                <div class="mt-8">
                    <a href="{{ route('public.daftar') }}"
                        class="group/btn flex items-center justify-center gap-2 w-full rounded-full py-3.5 px-6 text-sm font-bold transition-all duration-300 shadow-md hover:shadow-lg bg-primary-500 text-white hover:bg-primary-600">
                        Coba Gratis 14 Hari
                        <i data-lucide="arrow-right" class="w-4 h-4 group-hover/btn:translate-x-0.5 transition-transform"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        @if($modulePrices->isNotEmpty())
        @php
            $modulIcons = [
                'Keuangan (SPP & Tagihan)' => 'wallet',
                'e-Kantin' => 'shopping-cart',
                'Akademik' => 'book-open',
                'Absensi' => 'calendar-check',
                'PSB (Pendaftaran Siswa Baru)' => 'user-plus',
                'Tahfidz' => 'book-marked',
                'Perizinan' => 'door-open',
                'Konseling' => 'heart-handshake',
            ];
        @endphp
        <div class="mt-16 max-w-5xl mx-auto">
            <div class="text-center max-w-xl mx-auto mb-8">
                <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3.5 py-1.5 rounded-full border border-primary-100">Modul Tambahan</span>
                <h3 class="mt-4 text-2xl font-bold text-slate-900">Lengkapi Sesuai Kebutuhan</h3>
                <p class="mt-2 text-sm text-slate-500">Aktifkan satu-satu kapan saja dari halaman Langganan, tanpa terikat paket.</p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white shadow-xl p-5 md:p-7">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($modulePrices as $modul)
                    <div class="group flex items-center gap-4 rounded-2xl border border-slate-100 bg-slate-50/70 hover:bg-white hover:border-primary-300 hover:shadow-md transition-all duration-300 p-4">
                        <div class="w-11 h-11 rounded-xl bg-white shadow-sm text-primary-600 flex items-center justify-center shrink-0 group-hover:bg-primary-500 group-hover:text-white transition-all duration-300">
                            <i data-lucide="{{ $modulIcons[$modul->nama] ?? 'puzzle' }}" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $modul->nama }}</p>
                            @if($modul->is_gratis)
                                <p class="text-xs font-semibold text-emerald-600 mt-0.5">Gratis</p>
                            @else
                                <p class="text-xs font-semibold text-slate-500 mt-0.5">Rp{{ number_format($modul->harga_bulanan / 1000, 0) }}rb/bulan</p>
                            @endif
                        </div>
                        <span class="relative inline-flex h-5 w-9 items-center rounded-full bg-slate-200 group-hover:bg-primary-200 transition-colors shrink-0" aria-hidden="true">
                            <span class="inline-block h-3.5 w-3.5 translate-x-1 rounded-full bg-white shadow transition-transform group-hover:translate-x-4"></span>
                        </span>
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('public.daftar') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-all duration-300 text-sm font-bold py-3 px-8 shadow-md hover:shadow-lg">
                        Coba Gratis 14 Hari
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</section>


<!-- FAQ - dinamis -->
<section id="faq" class="py-20 bg-white border-t border-slate-100">
    <div class="mx-auto max-w-6xl px-4">
        <div class="text-center space-y-4 mb-16">
            <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3 py-1 rounded-full">Tanya Jawab</span>
            <h2 class="text-3xl font-extrabold text-slate-900 sm:text-4xl tracking-tight">Pertanyaan yang Sering Diajukan</h2>
            <p class="text-base text-slate-600">Temukan jawaban cepat atas pertanyaan mendasar mengenai penggunaan platform {{ $setting->brand_name }}.</p>
        </div>

        @php $faqChunks = $faqItems->chunk((int) max(1, ceil($faqItems->count() / 2))); @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
            @foreach($faqChunks as $chunk)
            <div class="space-y-4">
                @foreach($chunk as $faq)
                <div class="border border-slate-200 rounded-2xl bg-white overflow-hidden transition-all duration-300">
                    <button class="faq-btn w-full flex items-center justify-between p-6 text-left focus:outline-none">
                        <span class="font-bold text-slate-950 pr-4">{{ $faq->pertanyaan }}</span>
                        <span class="bg-primary-50 text-primary-500 p-1.5 rounded-full transition-transform duration-300 faq-icon-wrapper">
                            <i data-lucide="chevron-down" class="w-5 h-5"></i>
                        </span>
                    </button>
                    <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-slate-50/50">
                        <p class="p-6 text-sm text-slate-600 leading-relaxed border-t border-slate-100">{{ $faq->jawaban }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA FINAL -->
<section class="py-20 md:py-28 bg-slate-900 text-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#00A39D_1px,transparent_1px)] [background-size:16px_16px]"></div>
    <div class="relative mx-auto max-w-5xl px-4 text-center space-y-8">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-950 border border-primary-800 text-xs font-semibold text-primary-300 uppercase tracking-wider mx-auto">
            Langkah Digitalisasi Hebat
        </div>
        <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight max-w-3xl mx-auto leading-tight">
            Saatnya Lembaga Anda Bertransformasi ke Sistem Digital
        </h2>
        <p class="text-base sm:text-lg text-slate-300 max-w-2xl mx-auto font-medium">
            Nikmati kemudahan pengelolaan lembaga yang teratur, hemat ribuan jam kerja berharga administrasi, dan tingkatkan kepercayaan wali siswa Anda sekarang juga bersama {{ $setting->brand_name }}.
        </p>
        <div class="flex flex-wrap justify-center gap-4 text-sm font-semibold text-slate-300">
            <span class="flex items-center gap-1.5 px-3 py-1 bg-slate-800 rounded-full">
                <i data-lucide="clock" class="w-4 h-4 text-primary-400"></i> Hemat Waktu Staf
            </span>
            <span class="flex items-center gap-1.5 px-3 py-1 bg-slate-800 rounded-full">
                <i data-lucide="folder-check" class="w-4 h-4 text-primary-400"></i> Administrasi Lebih Rapi
            </span>
            <span class="flex items-center gap-1.5 px-3 py-1 bg-slate-800 rounded-full">
                <i data-lucide="eye" class="w-4 h-4 text-primary-400"></i> Transparan & Modern
            </span>
        </div>
        <div class="flex flex-col sm:flex-row justify-center items-center gap-4 pt-4">
            <a href="{{ route('public.daftar') }}" class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-full bg-primary-500 px-8 py-4 text-base font-bold text-white shadow-premium hover:bg-primary-600 hover:scale-105 active:scale-95 transition-all">
                Coba Demo Gratis Sekarang
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </a>
            <button onclick="hubungiSales()" class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-full bg-slate-800 border border-slate-700 px-8 py-4 text-base font-semibold text-white hover:bg-slate-700 transition-colors">
                <i data-lucide="phone" class="w-5 h-5 text-emerald-400"></i>
                Konsultasi via WhatsApp
            </button>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="bg-slate-950 text-slate-400 py-12 border-t border-slate-900 text-base">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
            <div class="space-y-4 col-span-1 md:col-span-2">
                @if($logoUrl)
                <div class="bg-white rounded-lg px-3 py-2 inline-flex">
                    <img src="{{ $logoUrl }}" alt="{{ $setting->brand_name }}" class="h-8 w-auto">
                </div>
                @else
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-500 text-white">
                        <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                    </div>
                    <span class="text-lg font-bold text-white">{{ $brandParts[0] }}<span class="text-primary-500">{{ $brandParts[1] ?? '' }}</span></span>
                </div>
                @endif

                <p class="max-w-sm text-sm leading-relaxed text-slate-500">
                    {{ $setting->brand_name }} menyediakan platform manajemen terintegrasi terlengkap guna mendorong modernisasi dan akselerasi keandalan digital di seluruh lembaga pendidikan Islam di Indonesia.
                </p>

                <div class="pt-2 space-y-3">
                    <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Temukan Kami di Media Sosial</h4>
                    <div class="flex gap-3">
                        @if($setting->ig_url)
                        <a href="{{ $setting->ig_url }}" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 rounded-full bg-slate-900 hover:bg-primary-500 text-slate-300 hover:text-white flex items-center justify-center border border-slate-800 hover:border-primary-500 transition-all duration-300 hover:scale-110 shadow-md">
                            <i class="fa-brands fa-instagram text-base"></i>
                        </a>
                        @endif
                        @if($setting->fb_url)
                        <a href="{{ $setting->fb_url }}" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 rounded-full bg-slate-900 hover:bg-primary-500 text-slate-300 hover:text-white flex items-center justify-center border border-slate-800 hover:border-primary-500 transition-all duration-300 hover:scale-110 shadow-md">
                            <i class="fa-brands fa-facebook-f text-base"></i>
                        </a>
                        @endif
                        @if($setting->yt_url)
                        <a href="{{ $setting->yt_url }}" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 rounded-full bg-slate-900 hover:bg-primary-500 text-slate-300 hover:text-white flex items-center justify-center border border-slate-800 hover:border-primary-500 transition-all duration-300 hover:scale-110 shadow-md">
                            <i class="fa-brands fa-youtube text-base"></i>
                        </a>
                        @endif
                        @if($setting->x_url)
                        <a href="{{ $setting->x_url }}" target="_blank" rel="noopener noreferrer"
                           class="w-9 h-9 rounded-full bg-slate-900 hover:bg-primary-500 text-slate-300 hover:text-white flex items-center justify-center border border-slate-800 hover:border-primary-500 transition-all duration-300 hover:scale-110 shadow-md">
                            <i class="fa-brands fa-x-twitter text-base"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-200">Navigasi Layanan</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#masalah" class="hover:text-primary-400 transition-colors">Analisis Masalah</a></li>
                    <li><a href="#solusi" class="hover:text-primary-400 transition-colors">Solusi Digital</a></li>
                    <li><a href="#fitur" class="hover:text-primary-400 transition-colors">Fitur Aplikasi</a></li>
                    <li><a href="#harga" class="hover:text-primary-400 transition-colors">Skema Investasi</a></li>
                </ul>
            </div>

            <div class="space-y-3">
                <h4 class="text-sm font-bold uppercase tracking-wider text-slate-200">Kontak Resmi</h4>
                @if($setting->footer_legalitas || $setting->nomor_nib || $setting->nomor_akta)
                <div class="text-sm text-slate-400 space-y-0.5">
                    @if($setting->footer_legalitas)
                    <p class="font-semibold text-slate-200">{{ $setting->footer_legalitas }}</p>
                    @endif
                    @if($setting->nomor_nib)
                    <p>NIB: {{ $setting->nomor_nib }}</p>
                    @endif
                    @if($setting->nomor_akta)
                    <p>No. Akta: {{ $setting->nomor_akta }}</p>
                    @endif
                </div>
                @endif
                <p class="text-sm text-slate-500 leading-relaxed">
                    {{ $setting->alamat }} <br>
                    @if($setting->email_kontak)
                    Email: <a href="mailto:{{ $setting->email_kontak }}" class="hover:text-white transition-colors">{{ $setting->email_kontak }}</a> <br>
                    @endif
                    @if($setting->whatsapp_number)
                    Telp/WA: <a href="https://wa.me/{{ $setting->whatsapp_number }}" class="hover:text-white transition-colors">{{ $setting->whatsapp_number }}</a>
                    @endif
                </p>
            </div>
        </div>

        <div class="pt-8 border-t border-slate-900 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-slate-500">
            <p>{{ $setting->footer_text ?? ('© '.date('Y').' '.$setting->brand_name.'. Hak Cipta Dilindungi Undang-Undang.') }}</p>
            <div class="flex gap-4">
                <a href="{{ route('legal.privasi') }}" class="hover:text-primary-400 transition-colors">Kebijakan Privasi</a>
                <a href="{{ route('legal.syarat-ketentuan') }}" class="hover:text-primary-400 transition-colors">Syarat & Ketentuan</a>
            </div>
        </div>
    </div>
</footer>

<script>
    lucide.createIcons();

    const waNumber = "{{ $setting->whatsapp_number }}";
    const waDefaultMessage = @json($setting->whatsapp_pesan_default ?? '');

    function hubungiSales() {
        const url = "https://wa.me/" + waNumber + "?text=" + encodeURIComponent(waDefaultMessage);
        window.open(url, "_blank");
    }

    // Slider testimoni (scroll-snap native, responsif otomatis)
    (function () {
        const scroller = document.getElementById('testimoni-scroll');
        if (!scroller) return;

        const cards = Array.from(scroller.children);
        const dots = document.querySelectorAll('.testimoni-dot');
        const prevBtn = document.getElementById('testimoni-prev');
        const nextBtn = document.getElementById('testimoni-next');
        let autoTimer;

        function step() {
            const card = cards[0];
            const style = getComputedStyle(scroller);
            const gap = parseFloat(style.columnGap || style.gap || 0);
            return card.getBoundingClientRect().width + gap;
        }

        function next() {
            const atEnd = scroller.scrollLeft + scroller.clientWidth >= scroller.scrollWidth - 10;
            scroller.scrollTo({ left: atEnd ? 0 : scroller.scrollLeft + step(), behavior: 'smooth' });
        }
        function prev() {
            scroller.scrollTo({ left: Math.max(0, scroller.scrollLeft - step()), behavior: 'smooth' });
        }
        function goTo(i) {
            scroller.scrollTo({ left: cards[i].offsetLeft - scroller.offsetLeft, behavior: 'smooth' });
        }

        function startAuto() {
            stopAuto();
            if (cards.length > 1) autoTimer = setInterval(next, 5500);
        }
        function stopAuto() {
            if (autoTimer) clearInterval(autoTimer);
        }

        nextBtn?.addEventListener('click', () => { next(); startAuto(); });
        prevBtn?.addEventListener('click', () => { prev(); startAuto(); });
        dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); startAuto(); }));

        scroller.addEventListener('mouseenter', stopAuto);
        scroller.addEventListener('mouseleave', startAuto);
        scroller.addEventListener('touchstart', stopAuto, { passive: true });

        // Update dot aktif berdasarkan kartu yang paling terlihat
        if (dots.length) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting && entry.intersectionRatio > 0.6) {
                        const idx = cards.indexOf(entry.target);
                        dots.forEach((d, i) => {
                            d.classList.toggle('w-6', i === idx);
                            d.classList.toggle('bg-primary-500', i === idx);
                            d.classList.toggle('w-2', i !== idx);
                            d.classList.toggle('bg-slate-300', i !== idx);
                        });
                    }
                });
            }, { root: scroller, threshold: [0.6] });
            cards.forEach((c) => observer.observe(c));
        }

        startAuto();
    })();

    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const menuIcon = document.getElementById('menu-icon');
    mobileMenuBtn.addEventListener('click', () => {
        const isHidden = mobileMenu.classList.contains('hidden');
        mobileMenu.classList.toggle('hidden');
        menuIcon.setAttribute('data-lucide', isHidden ? 'x' : 'menu');
        lucide.createIcons();
    });
    document.querySelectorAll('#mobile-menu a').forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            menuIcon.setAttribute('data-lucide', 'menu');
            lucide.createIcons();
        });
    });

    document.querySelectorAll('.faq-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const content = btn.nextElementSibling;
            const iconWrapper = btn.querySelector('.faq-icon-wrapper');
            const isOpen = content.style.maxHeight && content.style.maxHeight !== '0px';

            document.querySelectorAll('.faq-content').forEach(c => {
                c.style.maxHeight = '0px';
                c.parentElement.classList.remove('border-primary-500');
                c.parentElement.classList.add('border-slate-200');
            });
            document.querySelectorAll('.faq-icon-wrapper').forEach(w => {
                w.style.transform = 'rotate(0deg)';
                w.classList.remove('bg-primary-500', 'text-white');
                w.classList.add('bg-primary-50', 'text-primary-500');
            });

            if (!isOpen) {
                content.style.maxHeight = content.scrollHeight + 'px';
                btn.parentElement.classList.remove('border-slate-200');
                btn.parentElement.classList.add('border-primary-500');
                iconWrapper.style.transform = 'rotate(180deg)';
                iconWrapper.classList.remove('bg-primary-50', 'text-primary-500');
                iconWrapper.classList.add('bg-primary-500', 'text-white');
            }
        });
    });

    // Pop-up social proof - muncul bergantian di pojok kiri bawah
    (function () {
        const popup = document.getElementById('social-proof-popup');
        if (!popup) return;

        @php
            $buktiSosialEntries = $buktiSosialList->map(fn($b) => [
                'nama' => $b->lokasi ? $b->nama_lembaga.', '.$b->lokasi : $b->nama_lembaga,
                'waktu' => $b->waktuBergabungText(),
            ]);
        @endphp
        const entries = @json($buktiSosialEntries);
        if (!entries.length) return;

        const nameEl = document.getElementById('social-proof-name');
        const timeEl = document.getElementById('social-proof-time');
        let index = 0;
        let dismissedManually = false;
        let cycleTimer;

        function show() {
            if (dismissedManually) return;
            nameEl.textContent = entries[index].nama;
            timeEl.textContent = entries[index].waktu ? ' · ' + entries[index].waktu : '';
            popup.classList.remove('opacity-0', 'translate-y-3', 'pointer-events-none');
            popup.classList.add('opacity-100', 'translate-y-0');
            setTimeout(hide, 5500);
        }

        function hide() {
            popup.classList.add('opacity-0', 'translate-y-3', 'pointer-events-none');
            popup.classList.remove('opacity-100', 'translate-y-0');
        }

        window.dismissSocialProof = function () {
            dismissedManually = true;
            hide();
            clearTimeout(cycleTimer);
        };

        function cycle() {
            show();
            index = (index + 1) % entries.length;
            cycleTimer = setTimeout(cycle, 9000);
        }

        setTimeout(cycle, 4000);
    })();

    // Lightbox zoom untuk galeri "Tampilan Asli Aplikasi"
    (function () {
        const lightbox = document.getElementById('image-lightbox');
        if (!lightbox) return;
        const img = document.getElementById('lightbox-img');
        const caption = document.getElementById('lightbox-caption');

        window.openLightbox = function (src, title) {
            img.src = src;
            img.alt = title || '';
            caption.textContent = title || '';
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };
        window.closeLightbox = function () {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.body.style.overflow = '';
        };
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') window.closeLightbox();
        });
    })();

    // Animasi reveal saat kartu harga masuk viewport
    (function () {
        const els = document.querySelectorAll('.reveal-on-scroll');
        if (!els.length) return;
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        els.forEach((el) => observer.observe(el));
    })();
</script>
</body>
</html>
