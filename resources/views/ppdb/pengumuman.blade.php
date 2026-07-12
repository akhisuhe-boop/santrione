@extends('ppdb.layout.ppdb')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">

    {{-- HERO --}}
    <div class="px-6 pt-8 pb-6">

        <div class="flex items-start justify-between">

            <div>
                <h1 class="text-lg font-bold text-slate-900 tracking-tight flex items-center gap-2">

                    {{-- ICON --}}
                    <x-heroicon-o-megaphone class="w-6 h-6 text-[#00A39D]" />

                    Pengumuman

                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Informasi terbaru seputar proses Penerimaan Peserta Didik Baru
                </p>
            </div>

            {{-- LIVE --}}
            <div class="hidden sm:flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 shadow-sm">

                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute h-full w-full rounded-full bg-[#00A39D] opacity-75"></span>
                    <span class="relative h-2 w-2 rounded-full bg-[#00A39D]"></span>
                </span>

                <span class="text-xs text-slate-600 font-medium">
                    Live Updates
                </span>

            </div>

        </div>

    </div>

    {{-- FEED --}}
    <div class="px-6 pb-12">

        <div class="relative">

            {{-- TIMELINE LINE --}}
            <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-200"></div>

            <div class="space-y-6">

                @forelse($announcements as $item)

                <div class="relative pl-10 group">

                    {{-- DOT --}}
                    <div class="absolute left-2 top-4 w-4 h-4 rounded-full border-2 border-[#00A39D] bg-white
                                group-hover:bg-[#00A39D] transition"></div>

                    {{-- CARD --}}
<div class="rounded-2xl overflow-hidden
            bg-gradient-to-br
            from-[#F8FFFE]
            via-[#F0FDFA]
            to-[#E6FAF7]
            border border-[#00A39D]/10
            shadow-sm
            hover:shadow-lg
            hover:border-[#00A39D]/20
            hover:-translate-y-[2px]
            transition-all duration-300">

    <div class="p-5">

        {{-- HEADER --}}
        <div class="flex items-start justify-between gap-3 pb-4 border-b border-[#00A39D]/10">

            <h2 class="text-sm font-semibold text-[#134E4A] leading-snug">
                {{ $item->title }}
            </h2>

            <span class="shrink-0 text-[11px] px-2.5 py-1 rounded-full
                         bg-[#00A39D]/10
                         text-[#0F766E]
                         border border-[#00A39D]/10">
                {{ $item->created_at->diffForHumans() }}
            </span>

        </div>

        {{-- CONTENT --}}
        <div x-data="{ open: false }"
             class="mt-4 text-sm text-slate-600 leading-relaxed">

            <div>
                <span x-show="!open">
                    {!! \Illuminate\Support\Str::limit(strip_tags($item->content), 160) !!}
                </span>

                <span x-show="open">
                    {!! $item->content !!}
                </span>
            </div>

            <button
                @click="open = !open"
                class="mt-3 inline-flex items-center gap-1
                       text-xs font-semibold
                       text-[#0F766E]
                       hover:text-[#115E59]
                       transition">

                <span x-text="open ? 'Tutup' : 'Baca selengkapnya'"></span>

                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-4 h-4 transition-transform"
                     :class="{ 'rotate-180': open }"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke="currentColor">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>

            </button>

        </div>

        {{-- ATTACHMENT --}}
        @if($item->attachment)
            <div class="mt-4 flex items-center justify-between
                        p-3 rounded-xl
                        bg-white/70
                        border border-[#00A39D]/10">

                <div class="flex items-center gap-3">

                    <div class="flex items-center justify-center
                                w-10 h-10 rounded-lg
                                bg-[#00A39D]/10
                                border border-[#00A39D]/10">

                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5 text-[#0F766E]"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke-width="1.8"
                             stroke="currentColor">

                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  d="M19.5 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25A2.25 2.25 0 0 1 6.75 3h7.086a2.25 2.25 0 0 1 1.591.659l4.414 4.414A2.25 2.25 0 0 1 20.25 9.75v9A2.25 2.25 0 0 1 19.5 21Z" />
                        </svg>

                    </div>

                    <div>
                        <div class="text-xs font-semibold text-[#134E4A]">
                            Lampiran
                        </div>

                        <div class="text-[11px] text-[#0F766E]/70">
                            Klik untuk membuka file
                        </div>
                    </div>

                </div>

                <a href="{{ asset('storage/' . $item->attachment) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1
                          text-xs font-semibold
                          text-[#0F766E]
                          hover:text-[#115E59]
                          transition">

                    Lihat

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-3.5 h-3.5"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M9 5l7 7-7 7" />
                    </svg>

                </a>

            </div>
        @endif

    </div>
</div>

                            {{-- FOOTER --}}
                            <div class="mt-4 flex items-center justify-between">

                                @if($item->is_pinned ?? false)
                                    <span class="text-[10px] px-2 py-1 rounded-full bg-red-50 text-red-600 font-semibold">
                                        Penting
                                    </span>
                                @else
                                    <span></span>
                                @endif

                            </div>

                        </div>

                    </div>

                </div>

                @empty

                <div class="flex flex-col items-center justify-center py-20 text-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-14 h-14 text-slate-300 mb-3"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="1.5"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 12h6m-6 3h6m-3-9a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" />
                    </svg>

                    <h3 class="text-slate-700 font-semibold">
                        Belum Ada Informasi PPDB
                    </h3>
                    
                    <p class="text-sm text-slate-400 mt-1">
                        Informasi terbaru mengenai proses PPDB akan ditampilkan di halaman ini.
                    </p>

                </div>

                @endforelse

            </div>

        </div>

    </div>

</div>
@endsection