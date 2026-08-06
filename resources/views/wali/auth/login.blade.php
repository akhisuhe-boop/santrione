<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Portal Wali Santri</title>
    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <x-favicon />

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
            <div class="flex justify-center mb-3">

                @if(!empty($yayasan?->logo))
                    <img
                        src="{{ App\Support\FileUrlResolver::public($yayasan->logo) }}"
                        alt="{{ $yayasan->nama }}"
                        class="w-24 h-24 object-contain">
                @else
                    <img
                        src="{{ asset('images/qinara-apps-logo.png') }}"
                        alt="Qinara Apps"
                        class="w-40 object-contain">
                @endif

            </div>
        
            {{-- SUBTITLE --}}
            <p class="text-slate-500 text-sm">
                Portal Wali Santri
            </p>
        
            {{-- NAMA YAYASAN --}}
            <h1 class="mt-1 text-2xl font-bold text-[#00A39D] leading-tight">
                {{ $yayasan->nama ?? 'Qinara Apps' }}
            </h1>
        
            {{-- DESKRIPSI --}}
            <p class="mt-2 text-sm text-slate-500">
                Login menggunakan NIS atau NISN Santri
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

        {{-- FORM LOGIN --}}
        <form method="POST" action="{{ route('wali.authenticate') }}">
            @csrf

            {{-- NIS / NISN --}}
            <div class="mb-4">

                <label class="block mb-2 text-sm font-medium text-slate-700">
                    NIS / NISN
                </label>

                <div class="relative">

                    <div
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke-width="2"
                             stroke="currentColor"
                             class="w-5 h-5">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M15.75 6.75h-7.5m7.5 4.5h-7.5m7.5 4.5h-7.5" />
                        </svg>

                    </div>

                    <input
                        type="text"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="Masukkan NIS atau NISN"
                        required
                        class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-4 focus:outline-none focus:ring-2 focus:ring-teal-500"
                    >

                </div>

            </div>

            {{-- PASSWORD --}}
            <div
                class="mb-6"
                x-data="{ show:false }">

                <label class="block mb-2 text-sm font-medium text-slate-700">
                    Password
                </label>

                <div class="relative">

                    <div
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke-width="2"
                             stroke="currentColor"
                             class="w-5 h-5">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-1.5 0h12a1.5 1.5 0 011.5 1.5v7.5a1.5 1.5 0 01-1.5 1.5h-12A1.5 1.5 0 014.5 19.5V12a1.5 1.5 0 011.5-1.5z" />
                        </svg>

                    </div>

                    <input
                        :type="show ? 'text' : 'password'"
                        name="password"
                        placeholder="Masukkan Password"
                        required
                        class="w-full rounded-xl border border-slate-300 py-3 pl-11 pr-12 focus:outline-none focus:ring-2 focus:ring-teal-500"
                    >

                    {{-- SHOW PASSWORD --}}
                    <button
                        type="button"
                        @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">

                        <svg x-show="!show"
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke-width="2"
                             stroke="currentColor"
                             class="w-5 h-5">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.437 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>

                        <svg x-show="show"
                             x-cloak
                             xmlns="http://www.w3.org/2000/svg"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke-width="2"
                             stroke="currentColor"
                             class="w-5 h-5">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M3 3l18 18"/>
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M10.477 10.488a3 3 0 004.242 4.243"/>
                        </svg>

                    </button>

                </div>

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

                <span>Login</span>

            </button>

        </form>

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