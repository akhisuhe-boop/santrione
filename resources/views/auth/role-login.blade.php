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
    </style>
</head>

<body class="min-h-screen flex items-center justify-center px-4">

    <div class="w-full max-w-3xl">

        {{-- ========================= --}}
        {{-- HEADER --}}
        {{-- ========================= --}}

        <div class="text-center mb-14">

        {{-- LOGO YAYASAN --}}
        @if($yayasan && $yayasan->logo)
            <div class="flex justify-center mb-8">
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
        <div class="grid grid-cols-2 sm:grid-cols-2 gap-5">

            {{-- ADMIN --}}
            <a href="/admin/login"
               class="group bg-white/70 backdrop-blur-xl border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition hover:-translate-y-1">

                <div class="flex items-center gap-4">

                    <div class="w-12 h-12 rounded-xl bg-blue-500 flex items-center justify-center text-white shadow">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-6 h-6">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 0h10.5A2.25 2.25 0 0119.5 12.75v6A2.25 2.25 0 0117.25 21h-10.5A2.25 2.25 0 014.5 18.75v-6A2.25 2.25 0 016.75 10.5Z" />

                        </svg>

                    </div>

                    <div>
                        <h3 class="font-semibold text-slate-800 group-hover:text-blue-600">
                            Admin
                        </h3>
                        <p class="text-sm text-slate-500">
                            Manajemen sistem 
                        </p>
                    </div>

                </div>
            </a>

            {{-- WALI --}}
            <a href="{{ route('wali.login') }}"
               class="group bg-white/70 backdrop-blur-xl border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition hover:-translate-y-1">

                <div class="flex items-center gap-4">

                    <div class="w-12 h-12 rounded-xl bg-emerald-500 flex items-center justify-center text-white shadow">
                        {{-- Heroicon: users --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                             class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-4.663M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                        </svg>
                    </div>

                    <div>
                        <h3 class="font-semibold text-slate-800 group-hover:text-emerald-600">
                            Orang Tua / Wali
                        </h3>
                        <p class="text-sm text-slate-500">
                            Monitoring santri 
                        </p>
                    </div>

                </div>
            </a>

            {{-- GURU --}}
            <a href="/guru/login"
               class="group bg-white/70 backdrop-blur-xl border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition hover:-translate-y-1">

                <div class="flex items-center gap-4">

                    <div class="w-12 h-12 rounded-xl bg-teal-500 flex items-center justify-center text-white shadow">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-6 h-6">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 6.042A8.967 8.967 0 0 0 3.75 4.5v13.5A8.967 8.967 0 0 1 12 19.542m0-13.5A8.967 8.967 0 0 1 20.25 4.5v13.5A8.967 8.967 0 0 0 12 19.542m0-13.5v13.5" />
                        </svg>
                    </div>

                    <div>
                        <h3 class="font-semibold text-slate-800 group-hover:text-teal-600">
                            Guru
                        </h3>
                        <p class="text-sm text-slate-500">
                            Pembelajaran 
                        </p>
                    </div>

                </div>
            </a>

            {{-- PPDB --}}
            <a href="/ppdb/login"
               class="group bg-white/70 backdrop-blur-xl border border-slate-200 rounded-2xl p-5 shadow-sm hover:shadow-lg transition hover:-translate-y-1">

                <div class="flex items-center gap-4">

                    <div class="w-12 h-12 rounded-xl bg-orange-500 flex items-center justify-center text-white shadow">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor"
                            class="w-6 h-6">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5.25H7.5A2.25 2.25 0 0 0 5.25 7.5v11.25A2.25 2.25 0 0 0 7.5 21h9A2.25 2.25 0 0 0 18.75 18.75V7.5A2.25 2.25 0 0 0 16.5 5.25H15M9 5.25a3 3 0 0 1 6 0M9 5.25h6M9 12h6m-6 3h4.5"/>
                        </svg>
                    </div>

                    <div>
                        <h3 class="font-semibold text-slate-800 group-hover:text-orange-600">
                            PPDB
                        </h3>
                        <p class="text-sm text-slate-500">
                            Penerimaan siswa 
                        </p>
                    </div>

                </div>
            </a>

        </div>

        {{-- FOOTER --}}
        <div class="mt-14">
            <div class="max-w-xs mx-auto h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
            <div class="text-center pt-5">

                <p class="text-sm text-slate-500">
                    Powered by
                    <a href="https://www.qinaraindonesia.id"
                    target="_blank"
                    class="font-semibold text-[#00A39D] hover:text-[#00857f] transition">
                        Qinara Tech
                    </a>
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    SantriOne © {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>