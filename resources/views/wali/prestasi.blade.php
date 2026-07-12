@extends('wali.layout.wali')
@section('content')
@php
    $totalPrestasi = $siswa->prestasiSiswa->count();
    $totalPoint = $siswa->prestasiSiswa->sum('point');
    $terakhir = $siswa->prestasiSiswa()
    ->orderByDesc('tanggal')
    ->orderByDesc('id')
    ->first();
    $statusLabel = 'Berprestasi';
    if ($totalPoint >= 200) {
        $statusLabel = 'Sangat Berprestasi';
    }
    $prestasiList = $siswa->prestasiSiswa()
    ->orderByDesc('tanggal')
    ->orderByDesc('id')
    ->get();
@endphp

<div class="p-4 space-y-5">
    {{-- HERO --}}
    <div
        class="relative overflow-hidden rounded-[28px]
        bg-gradient-to-br
        from-[#00A39D]
        via-[#00B4AC]
        to-[#14C8C0]
        p-6 text-white shadow-xl">

        <div
            class="absolute top-0 right-0
            w-40 h-40 bg-white/10
            rounded-full -mr-16 -mt-16">
        </div>
        <div class="relative z-10">
            <div class="text-white/80 text-sm">
                Monitoring Prestasi
            </div>
            <h1 class="text-2xl font-bold">
                Prestasi Santri
            </h1>
            <p class="text-white/70 text-xs mt-1">
                Monitoring prestasi dan poin prestasi santri
            </p>

        {{-- SUMMARY --}}
        <div
            class="
                mt-2.5
                rounded-xl
                bg-white/10
                backdrop-blur-sm
                border border-white/10
                p-3
            "
        >
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-3xl font-bold leading-none">
                        {{ $totalPoint }}
                    </div>
                    <div class="text-white/70 text-xs mt-1">
                        Total Poin Prestasi
                    </div>
                </div>
                <div
                    class="
                        w-11 h-11
                        rounded-lg
                        bg-white/10
                        flex items-center justify-center
                    "
                >
                    @if($statusLabel == 'Baik')
                        <x-heroicon-o-check-circle
                            class="w-5 h-5 text-emerald-100" />
                    @elseif($statusLabel == 'Ringan')
                        <x-heroicon-o-exclamation-circle
                            class="w-5 h-5 text-yellow-100" />
                    @elseif($statusLabel == 'Sedang')
                        <x-heroicon-o-exclamation-triangle
                            class="w-5 h-5 text-orange-100" />
                    @else
                        <x-heroicon-o-shield-exclamation
                            class="w-5 h-5 text-red-100" />
                    @endif
                </div>
            </div>
            <div
            class="
                mt-2
                pt-2
                border-t border-white/10
                grid grid-cols-2
                gap-4
            "
        >
            <div>
                <div class="text-white/60 text-xs">
                    Total Prestasi
                </div>
                <div class="text-2xl font-bold mt-0.5">
                    {{ $totalPrestasi }}
                </div>
            </div>
            <div class="text-right">
                <div class="text-white/60 text-xs">
                    Status
                </div>
                <div class="text-xl font-bold mt-0.5">
                    {{ $statusLabel }}
                </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    {{-- PRESTASI TERAKHIR --}}
@if($terakhir)

<div
    class="
        relative
        overflow-hidden
        rounded-[28px]
        border border-slate-100
        bg-white
        shadow-sm
    ">

    <div
        class="
            absolute
            -top-12
            -right-12
            w-36
            h-36
            rounded-full
            bg-[#00A39D]/5
        ">
    </div>

    <div class="relative p-4">

        <div class="flex items-start justify-between">

            <div>

                <div
                    class="
                        inline-flex
                        items-center
                        gap-2
                        px-3 py-1
                        rounded-full
                        bg-[#00A39D]/10
                        text-[#00A39D]
                        text-xs
                        font-medium
                    ">

                    <x-heroicon-o-trophy
                        class="w-3.5 h-3.5" />

                    Prestasi Terakhir

                </div>

                <h3
                    class="
                        text-lg
                        font-bold
                        text-slate-900
                        mt-2
                    ">

                    {{ $terakhir->prestasi?->nama }}

                </h3>

                <p
                    class="
                        text-xs
                        text-slate-500
                        mt-0.5
                    ">

                    Catatan prestasi terbaru

                </p>

            </div>

            <div
                class="
                    w-12 h-12
                    rounded-2xl
                    bg-gradient-to-br
                    from-[#00A39D]/20
                    to-[#00A39D]/5
                    flex items-center justify-center
                ">

                <x-heroicon-o-trophy
                    class="
                        w-6 h-6
                        text-[#00A39D]
                    " />

            </div>

        </div>

        {{-- RINGKASAN --}}
        <div
            class="
                mt-3
                rounded-2xl
                bg-slate-50
                p-4
            ">

            <div class="flex justify-between">

                <div>

                    <div
                        class="
                            text-xs
                            text-slate-500
                        ">
                        Peringkat
                    </div>

                    <div
                        class="
                            text-lg
                            font-bold
                            text-[#00A39D]
                            mt-1
                        ">

                        {{ $terakhir->peringkat }}

                    </div>

                </div>

                <div class="text-right">

                    <div
                        class="
                            text-xs
                            text-slate-400
                        ">
                        Tingkat
                    </div>

                    <div
                        class="
                            font-semibold
                            text-slate-900
                            mt-1
                        ">

                        {{ $terakhir->tingkat }}

                    </div>

                </div>

            </div>

        </div>

        {{-- DETAIL --}}
        <div
            class="
                grid
                grid-cols-2
                gap-2
                mt-3
            ">

            <div
                class="
                    rounded-xl
                    border border-slate-100
                    p-3
                ">

                <div
                    class="
                        text-xs
                        text-slate-400
                    ">
                    Tanggal
                </div>

                <div
                    class="
                        flex
                        items-center
                        gap-1.5
                        mt-1.5
                        text-xs
                        font-medium
                        text-slate-900
                    ">

                    <x-heroicon-o-calendar
                        class="w-3.5 h-3.5 text-slate-400" />

                    {{ \Carbon\Carbon::parse($terakhir->tanggal)->format('d M Y') }}

                </div>

            </div>

            <div
                class="
                    rounded-xl
                    border border-slate-100
                    p-3
                ">

                <div
                    class="
                        text-xs
                        text-slate-400
                    ">
                    Poin
                </div>

                <div
                    class="
                        mt-1
                        text-xl
                        font-bold
                        text-[#00A39D]
                    ">

                    {{ $terakhir->point }}
                </div>
            </div>
        </div>

        @if($terakhir->keterangan)

        <div
            class="
                mt-3
                rounded-2xl
                border border-slate-100
                p-3
            ">

            <div
                class="
                    text-xs
                    text-slate-400
                    mb-1
                ">
                Keterangan
            </div>

            <div
                class="
                    text-sm
                    text-slate-700
                ">

                {{ $terakhir->keterangan }}
            </div>
        </div>
        @endif
    </div>
</div>
@endif

    {{-- ACTIVITY --}}
    <div
        x-data="{ showAll: false }"
        class="
            bg-white
            rounded-[28px]
            border border-slate-100
            shadow-sm
            overflow-hidden
        ">

        {{-- HEADER --}}
        <div class="px-4 py-3 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900">
                        Riwayat Prestasi
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Data prestasi santri
                    </p>
                </div>
                <div
                    class="
                        inline-flex
                        items-center
                        gap-2
                        px-2.5 py-1.5
                        rounded-2xl
                        bg-slate-50
                        text-slate-500
                        text-xs
                    ">
                    <x-heroicon-o-calendar
                        class="w-4 h-4" />
                    {{ $prestasiList->count() }}
                    Prestasi
                </div>
            </div>
        </div>

        {{-- LIST --}}
@forelse($prestasiList as $index => $item)

<div
    x-show="showAll || {{ $index }} < 3"
    x-transition.duration.200ms
    class="
        px-4 py-3
        hover:bg-slate-50/80
        transition
        {{ !$loop->last ? 'border-b border-slate-100' : '' }}
    ">

<div class="flex items-center justify-between">

    <div class="flex items-start gap-3">

        <div
            class="
                w-10 h-10
                rounded-xl
                bg-[#00A39D]/10
                flex items-center justify-center
                shrink-0
            ">

            <x-heroicon-o-trophy
                class="
                    w-5 h-5
                    text-[#00A39D]
                " />

        </div>

        <div>

            {{-- NAMA PRESTASI --}}
<div
    class="
        font-semibold
        text-sm
        text-slate-900
    ">

    {{ $item->prestasi?->nama }}

</div>

{{-- PERINGKAT & TINGKAT --}}
<div
    class="
        flex
        flex-wrap
        items-center
        gap-2
        mt-1.5
    ">

    <span
        class="
            inline-flex
            items-center
            gap-1
            px-2 py-1
            rounded-lg
            bg-amber-50
            text-amber-600
            text-xs
            font-medium
        ">

        <x-heroicon-o-trophy
            class="w-3 h-3" />

        {{ $item->peringkat }}

    </span>

    <span
        class="
            inline-flex
            items-center
            gap-1
            px-2 py-1
            rounded-lg
            bg-slate-100
            text-slate-600
            text-xs
            font-medium
        ">

        <x-heroicon-o-academic-cap
            class="w-3 h-3" />

        {{ $item->tingkat }}

    </span>

</div>

            {{-- TANGGAL --}}
            <div
                class="
                    flex
                    items-center
                    gap-1
                    mt-1.5
                    text-xs
                    text-slate-400
                ">

                <x-heroicon-o-calendar
                    class="w-3 h-3" />

                {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}

            </div>

            {{-- KETERANGAN --}}
            @if($item->keterangan)

                <div
                    class="
                        mt-2
                        text-xs
                        text-slate-500
                    ">

                    {{ $item->keterangan }}

                </div>

            @endif

        </div>

    </div>

    {{-- POINT --}}
    <div class="text-right shrink-0">

        <div
            class="
                text-3xl
                font-bold
                text-[#00A39D]
            ">

            {{ $item->point }}

        </div>

        <div
            class="
                text-xs
                text-slate-400
                mt-1
            ">

            Poin

        </div>

    </div>

</div>

</div>

@empty
    <div class="p-10">
        <div class="text-center">

            <div
                class="
                    w-16 h-16
                    rounded-3xl
                    bg-amber-50
                    mx-auto
                    flex items-center justify-center
                ">
                <x-heroicon-o-trophy
                    class="
                        w-8 h-8
                        text-amber-500
                    " />
            </div>

            <div
                class="
                    font-bold
                    text-slate-900
                    mt-4
                ">
                Belum Ada Prestasi
            </div>

            <div
                class="
                    text-sm
                    text-slate-500
                    mt-2
                ">
                Belum ada data prestasi yang tercatat.
            </div>

        </div>
    </div>
@endforelse
        @if($prestasiList->count() > 3)

        <div
            class="
                p-4
                border-t border-slate-100
                bg-slate-50/50
            ">
            <button
                x-on:click="showAll = !showAll"
                class="
                    w-full
                    py-3
                    rounded-2xl
                    bg-[#00A39D]/10
                    hover:bg-[#00A39D]/15
                    text-[#00A39D]
                    font-medium
                    text-sm
                    transition
                ">
                <span x-show="!showAll">
                    Lihat Semua Prestasi
                </span>
                <span x-show="showAll">
                    Tampilkan Lebih Sedikit
                </span>
            </button>
        </div>
        @endif
    </div>
</div>
@endsection