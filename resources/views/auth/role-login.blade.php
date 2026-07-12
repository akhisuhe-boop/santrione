<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Akses Login</title>

    {{-- FONT: Jakarta Plus Sans --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- TAILWIND --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- HEROICONS --}}
    <script src="https://unpkg.com/heroicons@2.1.5/24/outline"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top, #ecfdf5, #f8fafc);
        }
        
        #install-popup{
        transition: opacity .5s ease, transform .5s ease;
    }

    #install-popup.hide-popup{
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
    }
    </style>
    {{-- Favicon --}}
    @if($yayasan && $yayasan->logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $yayasan->logo) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $yayasan->logo) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#00A39D">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
</head>

<body class="min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-3xl">

        {{-- ========================= --}}
        {{-- HEADER --}}
        {{-- ========================= --}}

        <div class="text-center mb-8">

        {{-- LOGO YAYASAN --}}
        @if($yayasan && $yayasan->logo)
            <div class="flex justify-center mt-4 mb-4">
                <img
                    src="{{ asset('storage/' . $yayasan->logo) }}"
                    alt="{{ $yayasan->nama }}"
                    class="h-24 md:h-28 w-auto object-contain"
                >
            </div>
        @endif

        {{-- GREETING --}}
        <p class="text-sm md:text-lg font-semibold text-emerald-700">
            Selamat Datang di
        </p>

        {{-- NAMA YAYASAN --}}
        <h1 class="mt-3 text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-slate-900">
            {{ $yayasan->nama }}
        </h1>

        {{-- TAGLINE --}}
        <div class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-full
                    bg-emerald-50 border border-emerald-100">

            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>

            <span class="text-sm font-semibold text-emerald-700">
                Platform Pendidikan Terintegrasi
            </span>

        </div>

        {{-- DESKRIPSI --}}
        <p class="mt-5 max-w-2xl mx-auto px-6 md:px-10 text-sm md:text-base leading-relaxed text-slate-500">
            Menghubungkan akademik, administrasi, keuangan, dan komunikasi dalam
            satu platform digital yang modern, efisien, dan real-time untuk seluruh
            ekosistem pendidikan.
        </p>

        </div>

        {{-- ROLE CARDS --}}
        <div class="px-6 md:px-0">
            <div class="grid grid-cols-2 gap-4 max-w-md mx-auto">
        
                {{-- ADMIN --}}
                <a href="/admin/login"
                   class="group rounded-2xl
                          border border-teal-300/60
                          bg-white/20
                          backdrop-blur-xl
                          p-4
                          text-center
                          shadow-sm
                          transition-all duration-300
                          hover:bg-white/40
                          hover:border-teal-500
                          hover:shadow-xl
                          hover:-translate-y-1">
        
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10">
                        <x-heroicon-o-lock-closed class="w-5 h-5 text-blue-600" />
                    </div>
        
                    <h3 class="mt-3 text-base font-semibold text-slate-800">
                        Admin
                    </h3>
        
                    <p class="mt-1 text-xs text-slate-500">
                        Manajemen Sistem
                    </p>
        
                </a>
        
                {{-- ORANG TUA --}}
                <a href="{{ route('wali.login') }}"
                   class="group rounded-2xl
                          border border-teal-300/60
                          bg-white/20
                          backdrop-blur-xl
                          p-4
                          text-center
                          shadow-sm
                          transition-all duration-300
                          hover:bg-white/40
                          hover:border-teal-500
                          hover:shadow-xl
                          hover:-translate-y-1">
        
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10">
                        <x-heroicon-o-users class="w-5 h-5 text-emerald-600" />
                    </div>
        
                    <h3 class="mt-3 text-base font-semibold text-slate-800">
                        Orang Tua
                    </h3>
        
                    <p class="mt-1 text-xs text-slate-500">
                        Monitoring Santri
                    </p>
        
                </a>
        
                {{-- GURU --}}
                <a href="/guru/login"
                   class="group rounded-2xl
                          border border-teal-300/60
                          bg-white/20
                          backdrop-blur-xl
                          p-4
                          text-center
                          shadow-sm
                          transition-all duration-300
                          hover:bg-white/40
                          hover:border-teal-500
                          hover:shadow-xl
                          hover:-translate-y-1">
        
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10">
                        <x-heroicon-o-academic-cap class="w-5 h-5 text-cyan-600" />
                    </div>
        
                    <h3 class="mt-3 text-base font-semibold text-slate-800">
                        Guru
                    </h3>
        
                    <p class="mt-1 text-xs text-slate-500">
                        Pembelajaran
                    </p>
        
                </a>
        
                {{-- PPDB --}}
                <a href="/ppdb/login"
                   class="group rounded-2xl
                          border border-teal-300/60
                          bg-white/20
                          backdrop-blur-xl
                          p-4
                          text-center
                          shadow-sm
                          transition-all duration-300
                          hover:bg-white/40
                          hover:border-teal-500
                          hover:shadow-xl
                          hover:-translate-y-1">
        
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/10">
                        <x-heroicon-o-document-text class="w-5 h-5 text-orange-600" />
                    </div>
        
                    <h3 class="mt-3 text-base font-semibold text-slate-800">
                        PPDB
                    </h3>
        
                    <p class="mt-1 text-xs text-slate-500">
                        Pendaftaran
                    </p>
        
                </a>
        
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="mt-8">
            <div class="max-w-xs mx-auto h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
            <div class="text-center pt-5">

                <p class="text-sm text-slate-500">
                    Powered by
                    <a href="https://www.qinaraindonesia.id"
                    target="_blank"
                    class="font-semibold text-[#00A39D] hover:text-[#00857f] transition">
                        Qinara Indonesia
                    </a>
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    QinaraApps © {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>
    
{{-- INSTALL PWA POPUP --}}
<div
    id="install-popup"
    class="hidden fixed bottom-5 right-5 z-50
           w-[280px]
           rounded-2xl
           border border-slate-200/70
           bg-white/95
           backdrop-blur-xl
           shadow-xl
           overflow-hidden">

    <div class="p-4">

        <div class="flex items-start gap-3">

            {{-- ICON --}}
            <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">

                <x-heroicon-o-device-phone-mobile
                    class="w-5 h-5 text-[#00A39D]" />

            </div>

            <div class="flex-1 min-w-0">

                <div class="flex items-center justify-between">

                    <h3 class="text-sm font-semibold text-slate-900">
                        Install SantriOne
                    </h3>

                    <button
                        id="closeInstallPopup"
                        class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition">

                        <x-heroicon-o-x-mark class="w-4 h-4"/>

                    </button>

                </div>

                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Tambahkan ke layar utama agar akses lebih cepat.
                </p>

            </div>

        </div>

        <div class="mt-4 flex gap-2">

            <button
                id="installBtn"
                class="flex-1 rounded-lg
                       bg-[#00A39D]
                       px-3 py-2
                       text-xs font-semibold text-white
                       hover:bg-[#008B86]
                       transition">

                Install

            </button>

            <button
                id="laterInstallBtn"
                class="rounded-lg
                       px-3 py-2
                       text-xs font-medium
                       text-slate-600
                       hover:bg-slate-100
                       transition">

                Nanti

            </button>

        </div>

    </div>

</div>

<script>

let deferredPrompt = null;

const popup = document.getElementById('install-popup');


// Register Service Worker
if ('serviceWorker' in navigator) {

    window.addEventListener('load', () => {

        navigator.serviceWorker
            .register('/sw.js')
            .then(() => console.log('Service Worker Registered'))
            .catch(err => console.log(err));

    });

}


// Tampilkan popup hanya jika belum dibuka sebagai aplikasi PWA
window.addEventListener('load', () => {

    const isStandalone =
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true;

    if (!isStandalone) {

    popup?.classList.remove('hidden');

    setTimeout(() => {

    popup?.classList.add('hide-popup');

    setTimeout(() => {

        popup?.classList.add('hidden');

    }, 500);

}, 3000);

    }

});


// Simpan prompt install
window.addEventListener('beforeinstallprompt', (e) => {

    e.preventDefault();

    deferredPrompt = e;

});


// Tombol Install
document
    .getElementById('installBtn')
    ?.addEventListener('click', async () => {

        if (!deferredPrompt) {

            alert(
                'Aplikasi sudah terinstall atau gunakan browser lain'
            );

            return;
        }

        deferredPrompt.prompt();

        const result = await deferredPrompt.userChoice;

        console.log('Install result:', result);

        deferredPrompt = null;

        popup?.classList.add('hidden');

    });


// Tombol Close (X)
document
    .getElementById('closeInstallPopup')
    ?.addEventListener('click', () => {

        popup?.classList.add('hide-popup');

        setTimeout(() => {
            popup?.classList.add('hidden');
        }, 500);

    });


// Tombol Nanti
document
    .getElementById('laterInstallBtn')
    ?.addEventListener('click', () => {

        popup?.classList.add('hide-popup');

        setTimeout(() => {
            popup?.classList.add('hidden');
        }, 500);

    });


// Setelah berhasil install
window.addEventListener('appinstalled', () => {

    console.log('SantriOne berhasil diinstall');

    popup?.classList.add('hide-popup');

setTimeout(() => {
    popup?.classList.add('hidden');
}, 500);

});

</script>
    
</body>
</html>