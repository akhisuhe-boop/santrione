@extends('guru.layout.guru')

@section('content')

@php

$namaHari = [
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu'
];

$hariIni = now()->locale('id')->translatedFormat('l');

/*
|--------------------------------------------------------------------------
| Group Jadwal Berdasarkan Hari
|--------------------------------------------------------------------------
*/

$jadwalPerHari = collect();

foreach($namaHari as $hari){

    $jadwalPerHari[$hari] = $jadwal
    ->where('hari', $hari)
    ->sortBy(fn ($item) => $item->jamPelajaran?->urutan ?? 999);

}

$totalJadwal = $jadwal->count();
$totalJP = $jadwal->sum(fn ($item) => $item->jamPelajaran?->durasi_jp ?? 0);

@endphp

<div class="min-h-screen bg-[#F8FAFB] px-4 pb-8">

    {{-- ===================================================== --}}
    {{-- HEADER --}}
    {{-- ===================================================== --}}

    <div class="pt-5 mb-5">

        <div class="flex items-center justify-between">

            <div>

                <div class="text-xs text-slate-500">
                    Jadwal Mengajar
                </div>
            
                <div class="text-base font-bold text-slate-900 mt-0.5">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            
            </div>

            <div
                class="w-14 h-14 rounded-2xl bg-[#00A39D]/10 flex items-center justify-center">

                <x-heroicon-o-calendar-days
                    class="w-8 h-8 text-[#00A39D]" />

            </div>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- CARD SEMESTER --}}
    {{-- ===================================================== --}}

    <div
        class="relative overflow-hidden rounded-[30px]
               bg-gradient-to-br
               from-[#00A39D]
               via-[#00B4AC]
               to-[#14C8C0]
               p-6
               text-white
               shadow-xl
               mb-6">

        {{-- Ornament --}}
        <div
            class="absolute -right-10 -top-10
                   w-44 h-44 rounded-full
                   bg-white/10">
        </div>

        <div
            class="absolute -left-8 -bottom-8
                   w-28 h-28 rounded-full
                   bg-white/10">
        </div>

        <div class="relative z-10">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-sm text-white/80">
                        Semester Aktif
                    </div>

                    <div class="text-2xl font-bold mt-1">
                        {{ $semesterAktif->semester ?? '-' }}
                    </div>

                    <div class="text-sm text-white/70 mt-1">
                        {{ $semesterAktif->nama ?? '' }}
                    </div>

                </div>

                <div
                    class="w-16 h-16 rounded-3xl
                           bg-white/15
                           backdrop-blur
                           flex items-center justify-center">

                    <x-heroicon-o-academic-cap
                        class="w-9 h-9"/>

                </div>

            </div>

            <div class="grid grid-cols-3 gap-4 mt-6">

                <div
                    class="rounded-2xl
                           bg-white/10
                           border border-white/10
                           p-4">

                    <div class="text-xs text-white/70">
                        Jadwal
                    </div>

                    <div class="text-3xl font-bold mt-1">
                        {{ $totalJadwal }}
                    </div>

                </div>

                <div
                    class="rounded-2xl
                           bg-white/10
                           border border-white/10
                           p-4">

                    <div class="text-xs text-white/70">
                        Hari Aktif
                    </div>

                    <div class="text-2xl font-bold mt-1">
                        {{ $jadwalPerHari->filter(fn($x)=>$x->count())->count() }}
                    </div>

                </div>
                
                <div
                class="rounded-2xl
                       bg-white/10
                       border border-white/10
                       p-4">
            
                <div class="text-xs text-white/70">
                    Total JP
                </div>
            
                <div class="text-2xl font-bold mt-1">
                    {{ $totalJP }}
                </div>
            
            </div>

            </div>

        </div>

    </div>

    {{-- ===================================================== --}}
{{-- LIST PER HARI --}}
{{-- ===================================================== --}}

@foreach($namaHari as $hari)

@php
    $items = $jadwalPerHari[$hari];
    $isToday = $hari == $hariIni;
@endphp

<div
    class="mb-5 overflow-hidden rounded-3xl
           border {{ $isToday ? 'border-[#00A39D]/30 shadow-lg shadow-[#00A39D]/5' : 'border-slate-200' }}
           bg-white transition-all duration-300">

    {{-- HEADER --}}
    <div
        class="flex items-center justify-between
               border-b border-slate-100
               px-5 py-4">

        <div class="flex items-center gap-3">

            <div
                class="flex h-11 w-11 items-center justify-center rounded-2xl
                {{ $isToday
                    ? 'bg-[#00A39D] text-white'
                    : 'bg-slate-100 text-slate-500' }}">

                <x-heroicon-o-calendar-days class="w-5 h-5"/>

            </div>

            <div>

                <div class="flex items-center gap-2">

                    <h3 class="font-bold text-slate-900">

                        {{ $hari }}

                    </h3>

                    @if($isToday)

                        <span
                            class="rounded-full
                                   bg-[#00A39D]/10
                                   px-2.5
                                   py-1
                                   text-[10px]
                                   font-semibold
                                   text-[#00A39D]">

                            Hari Ini

                        </span>

                    @endif

                </div>

                <p class="mt-1 text-xs text-slate-500">

                    {{ $items->count() }}
                    Jadwal Mengajar

                </p>

            </div>

        </div>

        <span
            class="rounded-xl
                   bg-slate-100
                   px-3 py-1.5
                   text-xs
                   font-semibold
                   text-slate-600">

            {{ $items->count() }}
            Jadwal

        </span>

    </div>

    {{-- CONTENT --}}
    <div class="px-5 py-2">

        @forelse($items as $item)

        <div
            class="flex gap-4
                   {{ !$loop->last ? 'border-b border-slate-100 pb-4 mb-4' : 'py-2' }}">

            {{-- Timeline --}}
            <div class="flex flex-col items-center">

                <div
                    class="h-3 w-3 rounded-full bg-[#00A39D] ring-4 ring-[#00A39D]/10">
                </div>

                @if(!$loop->last)

                    <div
                        class="mt-2 w-px flex-1 bg-slate-200">
                    </div>

                @endif

            </div>

            {{-- Jam --}}
            <div class="w-32 flex-shrink-0">
            
                <div class="text-base font-semibold text-[#00A39D]">
                    {{ $item->jamPelajaran?->nama ?? '-' }}
                </div>
            
                <div class="mt-1 text-sm text-slate-500">
                    {{ \Carbon\Carbon::parse($item->jamPelajaran?->jam_mulai)->format('H:i') }}
                    -
                    {{ \Carbon\Carbon::parse($item->jamPelajaran?->jam_selesai)->format('H:i') }}
                </div>
            
            </div>
            
            {{-- Mata Pelajaran --}}
            <div class="flex-1">
            
                <div class="flex items-center justify-between gap-4">
            
                    <div class="min-w-0">
            
                        <div class="truncate text-base font-semibold text-slate-900">
                            {{ $item->mataPelajaran->nama ?? '-' }}
                        </div>
            
                        <div class="mt-1 flex items-center gap-2 text-sm text-slate-500">
            
                            <span>{{ $item->kelas->nama ?? '-' }}</span>
            
                            <span class="text-slate-300">•</span>
            
                            <span>{{ $item->jamPelajaran?->durasi_jp ?? '-' }} JP</span>
            
                        </div>
            
                    </div>

                </div>

            </div>

        </div>

        @empty

        <div class="py-10 text-center">

            <div
                class="mx-auto flex h-14 w-14
                       items-center justify-center
                       rounded-2xl
                       bg-slate-100">

                <x-heroicon-o-calendar-days
                    class="w-7 h-7 text-slate-400"/>

            </div>

            <div
                class="mt-4
                       font-semibold
                       text-slate-700">

                Tidak ada jadwal

            </div>

            <div
                class="mt-1
                       text-sm
                       text-slate-500">

                Tidak ada jadwal mengajar pada hari {{ $hari }}.

            </div>

        </div>

        @endforelse

    </div>

</div>

@endforeach

    {{-- ===================================================== --}}
    {{-- RINGKASAN --}}
    {{-- ===================================================== --}}

    <div
        class="mt-6
               rounded-[30px]
               overflow-hidden
               bg-gradient-to-br
               from-[#00A39D]
               via-[#00B4AC]
               to-[#14C8C0]
               shadow-xl">

        <div class="relative p-6">

            {{-- Ornament --}}
            <div
                class="absolute
                       -top-10
                       -right-10
                       w-40
                       h-40
                       rounded-full
                       bg-white/10">
            </div>

            <div
                class="absolute
                       -bottom-10
                       -left-10
                       w-28
                       h-28
                       rounded-full
                       bg-white/10">
            </div>

            <div class="relative z-10">

                <div class="flex items-center gap-3">

                    <div
                        class="w-14
                               h-14
                               rounded-2xl
                               bg-white/20
                               backdrop-blur
                               flex
                               items-center
                               justify-center">

                        <x-heroicon-o-chart-bar
                            class="w-8 h-8 text-white"/>

                    </div>

                    <div>

                        <div class="text-white/80 text-sm">
                            Ringkasan Jadwal
                        </div>

                        <div class="text-2xl font-bold text-white">
                            {{ $guru->nama }}
                        </div>

                    </div>

                </div>

                <div class="grid grid-cols-3 gap-4 mt-6">

                    {{-- Total Jadwal --}}
                    <div
                        class="rounded-2xl
                               bg-white/10
                               border border-white/10
                               p-4
                               text-center">

                        <div class="text-3xl font-bold text-white">
                            {{ $jadwal->count() }}
                        </div>

                        <div class="text-xs text-white/80 mt-1">
                            Total Jadwal
                        </div>

                    </div>

                    {{-- Hari Aktif --}}
                    <div
                        class="rounded-2xl
                               bg-white/10
                               border border-white/10
                               p-4
                               text-center">

                        <div class="text-3xl font-bold text-white">
                            {{ $jadwalPerHari->filter(fn($x) => $x->count())->count() }}
                        </div>

                        <div class="text-xs text-white/80 mt-1">
                            Hari Aktif
                        </div>

                    </div>

                    {{-- Semester --}}
                    <div
                        class="rounded-2xl
                               bg-white/10
                               border border-white/10
                               p-4
                               text-center">

                        <div class="text-lg font-bold text-white truncate">
                            {{ $semesterAktif->semester ?? '-' }}
                        </div>

                        <div class="text-xs text-white/80 mt-1">
                            Semester
                        </div>

                    </div>

                </div>

                {{-- INFO --}}
                <div
                    class="mt-6
                           rounded-2xl
                           bg-white/10
                           border
                           border-white/10
                           p-4">

                    <div class="flex items-start gap-3">

                        <div
                            class="w-10
                                   h-10
                                   rounded-xl
                                   bg-white/20
                                   flex
                                   items-center
                                   justify-center">

                            <x-heroicon-o-information-circle
                                class="w-6 h-6 text-white"/>

                        </div>

                        <div>

                            <div class="font-semibold text-white">
                                Informasi
                            </div>

                            <div class="text-sm text-white/80 mt-1 leading-relaxed">

                                Jadwal mengajar ditampilkan berdasarkan data yang telah
                                ditentukan oleh operator sekolah. Jika terdapat perubahan
                                jadwal atau kelas, silakan hubungi admin sekolah.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection