<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Portal PPDB</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Favicon --}}
    @if(!empty($yayasan?->logo))
        <link rel="icon" type="image/png" href="{{ App\Support\FileUrlResolver::public($yayasan->logo) }}">
        <link rel="shortcut icon" href="{{ App\Support\FileUrlResolver::public($yayasan->logo) }}">
        <link rel="apple-touch-icon" href="{{ App\Support\FileUrlResolver::public($yayasan->logo) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

</head>

<body class="font-sans bg-slate-100 min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-xs">

    <div class="bg-white rounded-3xl shadow-xl p-5">

        {{-- HEADER --}}
        <div class="text-center mb-8">

            {{-- LOGO --}}
            @if(!empty($yayasan?->logo))
                <div class="flex justify-center mb-3">
        
                    <img
                        src="{{ App\Support\FileUrlResolver::public($yayasan->logo) }}"
                        alt="{{ $yayasan->nama }}"
                        class="w-24 h-24 object-contain">
        
                </div>
            @endif
        
            {{-- SUBTITLE --}}
            <p class="text-slate-500 text-sm">
                Portal PPDB
            </p>
        
            {{-- NAMA YAYASAN --}}
            <h1 class="mt-1 text-2xl font-bold text-[#00A39D] leading-tight">
                {{ $yayasan->nama ?? 'Portal PPDB' }}
            </h1>
        
            {{-- DESKRIPSI --}}
            <p class="mt-2 text-sm text-slate-500 leading-6">
                Silakan isi data awal untuk membuat akun
                PPDB Online.
            </p>
        
        </div>

        {{-- ERROR --}}
        @if(session('error'))
            <div
                class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-600">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div
                class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-600">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- FORM REGISTER --}}
        <form method="POST" action="{{ route('ppdb.store') }}">
            @csrf

            {{-- NAMA LENGKAP --}}
            <div class="mb-4">
            
                <label class="block mb-2 text-sm font-medium text-slate-700">
                    Nama Lengkap
                </label>
            
                <input
                    type="text"
                    name="nama_lengkap"
                    value="{{ old('nama_lengkap') }}"
                    placeholder="Masukkan Nama Lengkap"
                    required
                    class="w-full rounded-xl border border-slate-300 py-3 px-4
                           focus:outline-none focus:ring-2 focus:ring-teal-500">
            
            </div>
            
            {{-- NISN --}}
            <div class="mb-4">
            
                <label class="block mb-2 text-sm font-medium text-slate-700">
                    NISN
                </label>
            
                <input
                    type="text"
                    name="nisn"
                    value="{{ old('nisn') }}"
                    placeholder="Masukkan NISN"
                    required
                    class="w-full rounded-xl border border-slate-300 py-3 px-4
                           focus:outline-none focus:ring-2 focus:ring-teal-500">
            
            </div>
            
            {{-- LEMBAGA --}}
            <div class="mb-4">
            
                <label class="block mb-2 text-sm font-medium text-slate-700">
                    Pilih Lembaga
                </label>
            
                <select
                    name="lembaga_id"
                    required
                    class="w-full rounded-xl border border-slate-300 py-3 px-4
                           focus:outline-none focus:ring-2 focus:ring-teal-500">
            
                    <option value="">-- Pilih Lembaga --</option>
            
                    @foreach($lembagas as $lembaga)
                        <option
                            value="{{ $lembaga->id }}"
                            @selected(old('lembaga_id') == $lembaga->id)>
                            {{ $lembaga->nama }}
                        </option>
                    @endforeach
            
                </select>
            
            </div>
            
            {{-- WA --}}
            <div class="mb-4">
            
                <label class="block mb-2 text-sm font-medium text-slate-700">
                    Nomor WhatsApp Orang Tua / Wali
                </label>
            
                <input
                    type="text"
                    name="wa_ayah"
                    value="{{ old('wa_ayah') }}"
                    placeholder="08xxxxxxxxxx"
                    required
                    class="w-full rounded-xl border border-slate-300 py-3 px-4
                           focus:outline-none focus:ring-2 focus:ring-teal-500">
            
            </div>
            
            {{-- ASAL SEKOLAH --}}
            <div class="mb-5">
            
                <label class="block mb-2 text-sm font-medium text-slate-700">
                    Asal Sekolah
                </label>
            
                <input
                    type="text"
                    name="asal_sekolah"
                    value="{{ old('asal_sekolah') }}"
                    placeholder="Contoh : SDIT Al-Fatih"
                    required
                    class="w-full rounded-xl border border-slate-300 py-3 px-4
                           focus:outline-none focus:ring-2 focus:ring-teal-500">
            
            </div>
            
            <div class="mb-5 rounded-2xl border border-blue-100 bg-blue-50 p-4">

                <h4 class="font-semibold text-blue-700">
            
                    Informasi
            
                </h4>
            
                <p class="mt-2 text-sm text-blue-600 leading-6">
            
                    Password awal akan otomatis menggunakan
                    <strong>NISN</strong>.
            
                    Setelah berhasil login,
                    password dapat diganti melalui menu
                    <strong>Profil</strong>.
            
                </p>
            
            </div>
            
            <div class="mb-6 flex items-start gap-3">

                <input
                    type="checkbox"
                    required
                    class="mt-1 rounded border-slate-300">
            
                <p class="text-sm text-slate-600 leading-6">
            
                    Saya menyatakan bahwa seluruh data
                    yang saya isi adalah benar dan dapat
                    dipertanggungjawabkan.
            
                </p>
            
            </div>

            {{-- BUTTON LOGIN --}}
            <button
                type="submit"
                class="w-full rounded-xl bg-teal-600 py-3 font-semibold text-white transition hover:bg-teal-700 flex items-center justify-center gap-2">

                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="2"
                     stroke="currentColor"
                     class="w-5 h-5">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15"/>
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M18 12H9m0 0l3-3m-3 3l3 3"/>
                </svg>

                <span>Daftar Sekarang</span>

            </button>
            <div class="mt-6 text-center">

                <p class="text-sm text-slate-500">
            
                    Sudah memiliki akun?
            
                    <a
                        href="{{ route('ppdb.login') }}"
                        class="font-semibold text-[#00A39D] hover:text-[#00857f]">
            
                        Login
            
                    </a>
            
                </p>
            
            </div>

        </form>
        
        <div class="mt-4 text-center">

            <a
                href="{{ route('login') }}"
                class="text-sm text-slate-500 hover:text-slate-700">
        
                ← Kembali ke Pilihan Portal
        
            </a>
        
        </div>

    </div>

    {{-- FOOTER --}}
        <div class="mt-9">
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

</body>
</html>