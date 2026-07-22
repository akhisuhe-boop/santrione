<div class="fixed bottom-0 left-1/2 -translate-x-1/2 z-50
            w-full max-w-full
            md:max-w-[768px]
            lg:max-w-[1024px]">

    <nav class="w-full bg-white border-t border-gray-200 shadow-sm">

        <div class="grid grid-cols-5 h-16 relative">

            {{-- DASHBOARD --}}
            <a href="{{ route('guru.dashboard') }}" wire:navigate
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('guru.dashboard') ? 'text-[#00A39D]' : 'text-gray-400' }}">

                @if(request()->routeIs('guru.dashboard'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-home class="w-6 h-6"/>

                <span class="text-xs mt-0.5 font-medium">
                    Beranda
                </span>

            </a>

            {{-- JADWAL --}}
            <a href="{{ route('guru.jadwal') }}" wire:navigate
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('guru.jadwal') ? 'text-[#00A39D]' : 'text-gray-400' }}">

                @if(request()->routeIs('guru.jadwal'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-calendar-days class="w-6 h-6"/>

                <span class="text-xs mt-0.5 font-medium">
                    Jadwal
                </span>

            </a>

            {{-- JURNAL --}}
            <div class="relative flex justify-center">

                <a href="{{ route('guru.jurnal') }}" wire:navigate
                   class="absolute -top-8
                          w-20 h-20
                          rounded-full
                          border-4 border-white
                          bg-[#00A39D]
                          shadow-xl shadow-[#00A39D]/40
                          flex items-center justify-center
                          text-white
                          transition hover:scale-105">

                    <x-heroicon-o-document-text class="w-9 h-9"/>

                </a>

            </div>

            {{-- ABSENSI --}}
            <a href="{{ route('guru.absensi') }}" wire:navigate
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('guru.absensi') ? 'text-[#00A39D]' : 'text-gray-400' }}">

                @if(request()->routeIs('guru.absensi'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-check-circle class="w-6 h-6"/>

                <span class="text-xs mt-0.5 font-medium">
                    Absensi
                </span>

            </a>

            {{-- PROFIL --}}
            <a href="{{ route('guru.profile') }}" wire:navigate
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('guru.profile') ? 'text-[#00A39D]' : 'text-gray-400' }}">

                @if(request()->routeIs('guru.profile'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-user-circle class="w-6 h-6"/>

                <span class="text-xs mt-0.5 font-medium">
                    Profil
                </span>

            </a>

        </div>

    </nav>

</div>