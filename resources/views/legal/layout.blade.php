<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $setting->brand_name }}</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        primary: {
                            50: '#E6F6F5', 100: '#CCECEB', 500: '#00A39D', 600: '#00938E', 900: '#00312F',
                        },
                    }
                }
            }
        }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-white text-slate-700 antialiased">

@php
    $brandParts = explode(' ', $setting->brand_name ?: 'Qinara Apps', 2);
    $logoUrl = $setting->logo ? \Illuminate\Support\Facades\Storage::disk('r2-public')->url($setting->logo) : null;
@endphp

<header class="border-b border-slate-100">
    <div class="mx-auto max-w-4xl px-4 lg:px-8 h-16 flex items-center justify-between">
        <a href="{{ route('landing.preview') }}" class="flex items-center gap-2">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $setting->brand_name }}" class="h-8 w-auto">
            @else
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary-500 text-white">
                    <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                </div>
                <span class="text-lg font-bold text-slate-900">{{ $brandParts[0] }}<span class="text-primary-500">{{ $brandParts[1] ?? '' }}</span></span>
            @endif
        </a>
        <a href="{{ route('landing.preview') }}" class="text-sm font-semibold text-slate-500 hover:text-primary-600 flex items-center gap-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Beranda
        </a>
    </div>
</header>

<main class="mx-auto max-w-4xl px-4 lg:px-8 py-16">
    <div class="mb-10">
        <span class="text-xs font-bold tracking-wider text-primary-500 bg-primary-50 px-3 py-1 rounded-full">@yield('title')</span>
        <h1 class="mt-4 text-3xl sm:text-4xl font-extrabold text-slate-900">@yield('title')</h1>
        <p class="mt-2 text-sm text-slate-400">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>
    </div>

    <div class="prose prose-slate max-w-none prose-headings:font-bold prose-headings:text-slate-900 prose-a:text-primary-600">
        @yield('content')
    </div>
</main>

<footer class="border-t border-slate-100 py-10 mt-10">
    <div class="mx-auto max-w-4xl px-4 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-slate-400">
        <p>{{ $setting->footer_text ?? ('© '.date('Y').' '.$setting->brand_name) }}</p>
        <div class="flex gap-4">
            <a href="{{ route('legal.privasi') }}" class="hover:text-primary-600">Kebijakan Privasi</a>
            <a href="{{ route('legal.syarat-ketentuan') }}" class="hover:text-primary-600">Syarat & Ketentuan</a>
        </div>
    </div>
</footer>

<script>lucide.createIcons();</script>
</body>
</html>
