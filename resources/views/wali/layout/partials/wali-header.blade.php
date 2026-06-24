<header
    class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-100"
>
    <div class="px-4 py-4 flex items-center justify-between gap-3">

        {{-- BACK --}}
        <button
            onclick="history.back()"
            class="w-10 h-10 rounded-xl bg-[#00A39D]/10 flex items-center justify-center hover:bg-[#00A39D]/20 transition"
        >
            <x-heroicon-o-chevron-left class="w-5 h-5 text-[#00A39D]" />
        </button>

        {{-- TITLE --}}
        <div class="flex-1 text-center px-2">
            <p class="text-xs text-slate-500 font-medium">
                Portal Wali Santri
            </p>
            <h1 class="text-base font-bold text-slate-900 truncate">
                {{ $yayasan->nama ?? 'Nama Yayasan' }}
            </h1>
        </div>

        @php
            $notifCount = isset($pengumuman) ? $pengumuman->count() : 0;
        @endphp

        {{-- ACTIONS --}}
        <div class="flex items-center gap-2">

            {{-- NOTIF --}}
            <div x-data="{ open: false }" class="relative">

                <button
                    @click="open = !open"
                    class="relative w-10 h-10 rounded-xl bg-[#00A39D]/10 border border-[#00A39D]/20 flex items-center justify-center hover:bg-[#00A39D]/20 transition"
                >
                    <x-heroicon-o-bell class="w-5 h-5 text-[#00A39D]" />

                    {{-- BADGE --}}
                    @if($notifCount > 0)
                        <span
                            class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1
                                   bg-red-500 text-white text-[10px] font-bold
                                   rounded-full flex items-center justify-center"
                        >
                            {{ $notifCount }}
                        </span>
                    @endif
                </button>

                {{-- DROPDOWN --}}
                <div
                    x-show="open"
                    @click.away="open = false"
                    x-transition
                    class="absolute right-0 mt-3 w-80 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden z-50"
                >

                    {{-- HEADER --}}
                    <div class="p-4 border-b bg-slate-50">
                        <div class="font-semibold text-sm text-slate-900">
                            Pengumuman
                        </div>
                        <div class="text-xs text-slate-500">
                            Notifikasi terbaru
                        </div>
                    </div>

                    {{-- LIST --}}
                    <div class="max-h-72 overflow-y-auto">

                        @forelse(($pengumuman ?? collect()) as $item)

                            <a href="{{ route('wali.pengumuman') }}"
                               class="block px-4 py-3 hover:bg-teal-50 transition">

                                <div class="font-semibold text-sm text-slate-900 line-clamp-1">
                                    {{ $item->title }}
                                </div>

                                <div class="text-xs text-slate-500 mt-1 line-clamp-2">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($item->content), 70) }}
                                </div>

                            </a>

                        @empty

                            <div class="p-4 text-sm text-slate-500 text-center">
                                Tidak ada pengumuman
                            </div>

                        @endforelse

                    </div>

                    {{-- FOOTER --}}
                    <a
                        href="{{ route('wali.pengumuman') }}"
                        class="block text-center p-3 text-sm font-semibold text-[#00A39D] border-t hover:bg-slate-50"
                    >
                        Lihat semua
                    </a>

                </div>
            </div>

            {{-- LOGOUT --}}
            <form method="POST" action="{{ route('wali.logout') }}">
                @csrf

                <button
                    type="submit"
                    class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center hover:bg-red-100 transition"
                >
                    <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-red-500" />
                </button>

            </form>

        </div>
    </div>
</header>