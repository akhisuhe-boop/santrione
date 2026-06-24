<div class="fixed bottom-0 left-1/2 -translate-x-1/2 z-50
            w-full max-w-full
            md:max-w-[768px]
            lg:max-w-[1024px]">

    <nav class="w-full bg-white border-t border-gray-200 shadow-sm">

        {{-- ❗ DIPERKECIL: h-20 → h-16 --}}
        <div class="grid grid-cols-5 h-16 relative">

            {{-- BERANDA --}}
            <a href="{{ route('wali.dashboard') }}"
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('wali.dashboard') ? 'text-[#00A39D]' : 'text-gray-400' }}">

                @if(request()->routeIs('wali.dashboard'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <svg class="w-6 h-6"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M3 10.5L12 3l9 7.5M5.25 9.75v9A1.5 1.5 0 006.75 20.25h10.5a1.5 1.5 0 001.5-1.5v-9"/>
                </svg>

                {{-- DIPERKECIL SPACING --}}
                <span class="text-xs mt-0.5 font-medium">
                    Beranda
                </span>

            </a>

            {{-- KEUANGAN --}}
            <a href="{{ route('wali.keuangan') }}"
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('wali.keuangan') ? 'text-[#00A39D]' : 'text-gray-400' }}">

                @php
                    $jumlahTagihan = isset($tagihanAktif) ? $tagihanAktif->count() : 0;
                @endphp

                @if($jumlahTagihan > 0)
                    <span class="absolute top-1 right-5 bg-red-500 text-white text-[10px]
                                min-w-[18px] h-[18px] rounded-full flex items-center justify-center font-bold">
                        {{ $jumlahTagihan }}
                    </span>
                @endif

                @if(request()->routeIs('wali.keuangan'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-credit-card class="w-6 h-6" />

                <span class="text-xs mt-0.5 font-medium">
                    Keuangan
                </span>

            </a>

            {{-- TOP UP --}}
            <div class="relative flex justify-center">

                {{-- ❗ DIPERBAIKI: -top-10 → -top-8 --}}
                <a href="{{ route('wali.topup') }}"
                    class="absolute -top-8

                        w-20 h-20
                        rounded-full
                        border-4 border-white
                        bg-[#00A39D]
                        shadow-xl shadow-[#00A39D]/40
                        flex items-center justify-center

                        text-white
                        transition hover:scale-105
                    "
                >
                    <x-heroicon-o-qr-code class="w-9 h-9" />
                </a>

            </div>

            {{-- RAPORT --}}
            <a href="{{ route('wali.raport') }}"
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('wali.raport') ? 'text-[#00A39D]' : 'text-gray-400' }}">

                @if(request()->routeIs('wali.raport'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <svg class="w-6 h-6"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0"/>
                </svg>

                <span class="text-xs mt-0.5 font-medium">
                    Raport
                </span>

            </a>

            {{-- PROFIL --}}
            <a href="{{ route('wali.profil') }}"
               class="flex flex-col items-center justify-center text-gray-400 relative">

                @if(request()->routeIs('wali.profil'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <svg class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M5.121 17.804A9 9 0 1118.88 17.8M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>

                <span class="text-xs mt-0.5 font-medium">
                    Profil
                </span>

            </a>

        </div>

    </nav>

</div>