@extends('wali.layout.wali')

@section('content')

<div class="p-4 space-y-5">

    @php

        $progress = $siswa->progress_tahfidz['progress'] ?? 0;
        $targetJuz = $siswa->progress_tahfidz['juz'] ?? '-';
        $status = $siswa->progress_tahfidz['status'] ?? '-';

        $setoranTerakhir = $siswa->tahfidzSetoran()
        ->orderByDesc('tanggal')
        ->orderByDesc('id')
        ->first();

    @endphp

    {{-- HERO HEADER --}}
    <div
        class="relative overflow-hidden rounded-[28px]
        bg-gradient-to-br from-[#00A39D]
        via-[#00B4AC]
        to-[#14C8C0]
        p-6 text-white shadow-xl">

        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-16 -mt-16"></div>

        <div class="relative z-10">

            <div class="text-white/80 text-sm">
                Tahfidz Monitoring
            </div>

            <h1 class="text-2xl font-bold mt-1">
                {{ $targetJuz }}
            </h1>

            <p class="text-white/80 text-sm mt-1">
                Progress hafalan Al-Qur'an santri
            </p>

            <div class="mt-5">

                <div class="flex justify-between text-sm mb-2">

                    <span>
                        Progress
                    </span>

                    <span class="font-semibold">
                        {{ $progress }}%
                    </span>

                </div>

                <div class="w-full bg-white/20 rounded-full h-3">

                    <div
                        class="h-3 bg-white rounded-full"
                        style="width: {{ $progress }}%">
                    </div>

                </div>

            </div>

            <div class="mt-4">

                <span
                    class="inline-flex items-center px-4 py-2 rounded-2xl bg-white/20 backdrop-blur text-xs font-medium">

                    {{ $status }}

                </span>

            </div>

        </div>

    </div>

    {{-- LAST DEPOSIT --}}
    @if($setoranTerakhir)

    <div
        class="
            relative
            overflow-hidden
            rounded-[28px]
            border border-slate-100
            bg-white
            shadow-sm
        "
    >

        {{-- Background Decoration --}}
        <div
            class="
                absolute
                -top-12
                -right-12
                w-36
                h-36
                rounded-full
                bg-[#00A39D]/5
            "
        ></div>

        <div class="relative p-4">

            {{-- Header --}}
            <div class="flex items-start justify-between">

                <div>

                    <div
                        class="
                            inline-flex
                            items-center
                            gap-2
                            px-3
                            py-1
                            rounded-full
                            bg-[#00A39D]/10
                            text-[#00A39D]
                            text-xs
                            font-medium
                        "
                    >

                        <x-heroicon-o-book-open
                            class="w-3.5 h-3.5"
                        />

                        Setoran Terakhir

                    </div>

                    <h3
                        class="
                            text-lg
                            font-bold
                            text-slate-900
                            mt-2
                        "
                    >
                        {{ $setoranTerakhir->surah?->nama ?? '-' }}
                    </h3>

                    <p
                        class="
                            text-xs
                            text-slate-500
                            mt-0.5
                        "
                    >
                        Hafalan terbaru santri
                    </p>

                </div>

                <div
                    class="
                        w-12
                        h-12
                        rounded-2xl
                        bg-gradient-to-br
                        from-[#00A39D]/20
                        to-[#00A39D]/5
                        flex
                        items-center
                        justify-center
                    "
                >

                    <x-heroicon-o-book-open
                        class="
                            w-6
                            h-6
                            text-[#00A39D]
                        "
                    />

                </div>

            </div>

            {{-- Score --}}
            <div
                class="
                    mt-3
                    flex
                    items-center
                    justify-between
                    rounded-2xl
                    bg-slate-50
                    p-3
                "
            >

                <div>

                    <div
                        class="
                            text-xs
                            text-slate-500
                        "
                    >
                        Nilai Setoran
                    </div>

                    <div
                        class="
                            text-3xl
                            font-bold
                            leading-none
                            text-[#00A39D]
                            mt-1
                        "
                    >
                        {{ $setoranTerakhir->nilai }}
                    </div>

                </div>

                <div class="text-right">

                    <div class="text-xs text-slate-400">
                        Rentang Ayat
                    </div>
                    
                    <div class="text-base font-semibold text-slate-900 mt-0.5">
                        {{ $setoranTerakhir->ayat_dari }}
                        -
                        {{ $setoranTerakhir->ayat_sampai }}
                    </div>
                    
                    <div class="text-xs text-slate-400">
                        {{ $setoranTerakhir->jumlah_ayat }} ayat
                    </div>

                </div>

            </div>

            {{-- Meta --}}
            <div
                class="
                    grid
                    grid-cols-2
                    gap-2
                    mt-3
                "
            >

                {{-- Jenis --}}
                <div
                    class="
                        rounded-xl
                        border
                        border-slate-100
                        p-3
                    "
                >

                    <div
                        class="
                            text-xs
                            text-slate-400
                        "
                    >
                        Jenis Setoran
                    </div>

                    <div class="mt-1.5">

                        <span
                            class="
                                inline-flex
                                px-2.5
                                py-1
                                rounded-full
                                bg-[#00A39D]/10
                                text-[#00A39D]
                                text-xs
                                font-medium
                            "
                        >
                            {{ ucfirst($setoranTerakhir->jenis) }}
                        </span>

                    </div>

                </div>

                {{-- Tanggal --}}
                <div
                    class="
                        rounded-xl
                        border
                        border-slate-100
                        p-3
                    "
                >

                    <div
                        class="
                            text-xs
                            text-slate-400
                        "
                    >
                        Tanggal Setoran
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
                        "
                    >

                        <x-heroicon-o-calendar
                            class="w-3.5 h-3.5 text-slate-400"
                        />

                        {{ \Carbon\Carbon::parse($setoranTerakhir->tanggal)->format('d M Y') }}

                    </div>

                </div>

            </div>

        </div>

    </div>

    @endif

    {{-- ACTIVITY --}}
    @php
    $setoranList = $siswa->tahfidzSetoran()
        ->orderByDesc('tanggal')
        ->orderByDesc('id')
        ->get();
    @endphp

    <div
        x-data="{ showAll: false }"
        class="
            bg-white
            rounded-[28px]
            border border-slate-100
            shadow-sm
            overflow-hidden
        "
    >

        {{-- HEADER --}}
        <div class="px-4 py-3 border-b border-slate-100">

            <div class="flex items-center justify-between">

                <div>

                    <h3 class="font-bold text-slate-900">
                        Aktivitas Setoran
                    </h3>

                    <p class="text-xs text-slate-500 mt-1">
                        Riwayat setoran hafalan terbaru
                    </p>

                </div>

                <div
                    class="
                        inline-flex
                        items-center
                        gap-2
                        px-2.5
                        py-1.5
                        rounded-2xl
                        bg-slate-50
                        text-slate-500
                        text-xs
                    "
                >

                    <x-heroicon-o-calendar
                        class="w-4 h-4"
                    />

                    {{ $setoranList->count() }}
                    Setoran

                </div>

            </div>

        </div>

        {{-- LIST --}}
        @forelse($setoranList as $index => $setoran)

            <div

                x-show="showAll || {{ $index }} < 3"

                x-transition.duration.200ms

                class="
                    px-4
                    py-3
                    hover:bg-slate-50/80
                    transition
                    {{ !$loop->last ? 'border-b border-slate-100' : '' }}
                "

            >

                <div class="flex items-center justify-between">

                    {{-- LEFT --}}
                    <div class="flex items-center gap-3">

                        <div
                            class="
                                w-10
                                h-10
                                rounded-xl
                                bg-[#00A39D]/10
                                flex
                                items-center
                                justify-center
                                shrink-0
                            "
                        >

                            <x-heroicon-o-book-open
                                class="
                                    w-5
                                    h-5
                                    text-[#00A39D]
                                "
                            />

                        </div>

                        <div class="leading-tight">

                            <div
                                class="
                                    font-semibold
                                    text-sm
                                    text-slate-900
                                "
                            >

                                {{ $setoran->surah?->nama ?? '-' }}

                            </div>

                            <div
                                class="
                                    text-xs
                                    text-slate-500
                                    mt-0.5
                                "
                            >

                                {{ ucfirst($setoran->jenis) }}
                                •
                                Juz {{ $setoran->juz?->nama ?? '-' }}

                            </div>

                            <div
                                class="
                                    flex
                                    items-center
                                    gap-1
                                    text-[11px]
                                    text-slate-400
                                    mt-0.5
                                "
                            >

                                <x-heroicon-o-calendar
                                    class="w-3 h-3"
                                />

                                {{ \Carbon\Carbon::parse($setoran->tanggal)->format('d M Y') }}

                            </div>

                        </div>

                    </div>

                    {{-- RIGHT --}}
                    <div class="text-right leading-tight">

                        @if($setoran->nilai)

                            <div
                                class="
                                    text-xl
                                    font-bold
                                    text-[#00A39D]
                                "
                            >

                                {{ $setoran->nilai }}

                            </div>

                        @endif

                        <div
                            class="
                                text-[11px]
                                text-slate-400
                                mt-0.5
                            "
                        >

                            <div class="text-[11px] text-slate-400 mt-0.5">
                                Ayat {{ $setoran->ayat_dari }} - {{ $setoran->ayat_sampai }}
                            </div>
                            
                            <div class="text-[11px] text-slate-400">
                                {{ $setoran->jumlah_ayat }} ayat
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        @empty

            <div class="p-8">

                <x-wali.empty
                    title="Belum Ada Setoran"
                    description="Data setoran tahfidz belum tersedia."
                />

            </div>

        @endforelse

        {{-- SHOW MORE --}}
        @if($setoranList->count() > 3)

            <div
                class="
                    p-4
                    border-t
                    border-slate-100
                    bg-slate-50/50
                "
            >

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
                    "

                >

                    <span x-show="!showAll">
                        Lihat Semua Aktivitas
                    </span>

                    <span x-show="showAll">
                        Tampilkan Lebih Sedikit
                    </span>

                </button>

            </div>

        @endif

    </div>

    </div>

</div>

@endsection