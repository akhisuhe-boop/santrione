{{-- BOTTOM NAVIGATION PPDB --}}
<div class="fixed bottom-0 left-1/2 -translate-x-1/2 z-50
            w-full max-w-full
            md:max-w-[768px]
            lg:max-w-[1024px]">

    <nav class="w-full bg-white border-t border-slate-200 shadow-sm">

        <div class="grid grid-cols-5 h-16 relative">

            {{-- BERANDA --}}
            <a href="{{ route('ppdb.dashboard') }}" wire:navigate
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('ppdb.dashboard') ? 'text-[#00A39D]' : 'text-slate-400' }}">

                @if(request()->routeIs('ppdb.dashboard'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-home class="w-6 h-6"/>

                <span class="mt-0.5 text-[11px] font-medium">
                    Beranda
                </span>

            </a>

            {{-- FORMULIR --}}
            <a href="{{ route('ppdb.formulir') }}" wire:navigate
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('ppdb.formulir') ? 'text-[#00A39D]' : 'text-slate-400' }}">

                @if(request()->routeIs('ppdb.formulir'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-document-text class="w-6 h-6"/>

                <span class="mt-0.5 text-[11px] font-medium">
                    Formulir
                </span>

            </a>

            {{-- PEMBAYARAN (CENTER ACTION) --}}
            <div class="relative flex justify-center">

                <a href="{{ route('ppdb.pembayaran') }}" wire:navigate
                   class="absolute -top-8
                          w-20 h-20
                          rounded-full
                          border-4 border-white
                          bg-[#00A39D]
                          shadow-xl shadow-[#00A39D]/30
                          flex items-center justify-center
                          text-white
                          transition duration-300
                          hover:scale-105">

                    <x-heroicon-o-credit-card class="w-9 h-9"/>

                </a>

            </div>

            {{-- INFORMASI --}}
            <a href="{{ route('ppdb.pengumuman') }}" wire:navigate
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('ppdb.pengumuman*') ? 'text-[#00A39D]' : 'text-slate-400' }}">

                @if(request()->routeIs('ppdb.pengumuman*'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-megaphone class="w-6 h-6"/>

                <span class="mt-0.5 text-[11px] font-medium">
                    Info
                </span>

            </a>

            {{-- PROFIL --}}
            <a href="{{ route('ppdb.profil') }}" wire:navigate
               class="flex flex-col items-center justify-center relative
               {{ request()->routeIs('ppdb.profil') ? 'text-[#00A39D]' : 'text-slate-400' }}">

                @if(request()->routeIs('ppdb.profil'))
                    <span class="absolute top-1 w-1.5 h-1.5 rounded-full bg-[#00A39D]"></span>
                @endif

                <x-heroicon-o-user-circle class="w-6 h-6"/>

                <span class="mt-0.5 text-[11px] font-medium">
                    Profil
                </span>

            </a>

        </div>

    </nav>

</div>