@extends('wali.layout.wali')

@section('content')

<div
    class="p-4 bg-slate-50 min-h-screen"
    x-data="{ tab:'all' }">

    @php
    // ===============================
    // 1. SATUKAN SEMUA ABSENSI
    // ===============================
    $allAbsensi = $siswa->absensis
        ->merge($siswa->absensiMapels)
        ->map(function ($item) {
            return (object)[
                'status' => trim($item->status),
            ];
        });

    // ===============================
    // 2. NORMALIZED COUNT
    // ===============================
    $hadir = $allAbsensi->where('status', 'Hadir')->count();
    $izin  = $allAbsensi->where('status', 'Izin')->count();
    $sakit = $allAbsensi->where('status', 'Sakit')->count();
    $terlambat = $allAbsensi->where('status', 'Terlambat')->count();

    // ===============================
    // 3. FIX TYPO STATUS (FINAL STANDARD = ALPA)
    // ===============================
    $alpha = $allAbsensi->where('status', 'Alpa')->count();

    // ===============================
    // 4. TOTAL & PERSENTASE
    // ===============================
    $total = $allAbsensi->count();

    $persentase = $total > 0
        ? round(($hadir / $total) * 100)
        : 0;
    @endphp

{{-- HERO HEADER --}}
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#00A39D] via-[#00B4AC] to-[#14C8C0] p-6 text-white shadow-lg mb-5">

    {{-- decoration --}}
    <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-white/5 rounded-full"></div>

    <div class="relative z-10">

        <div class="text-white/80 text-sm">
            Attendance Monitoring
        </div>

        <h1 class="text-2xl font-bold mt-1">
            Absensi Santri
        </h1>

        <p class="text-white/80 text-sm mt-1">
            Rekapitulasi Absensi Kegiatan & Belajar Santri
        </p>

        {{-- PROGRESS --}}
        <div class="mt-6">

            <div class="flex justify-between text-sm mb-2">
                <span class="text-white/80">Tingkat Kehadiran</span>
                <span class="font-semibold">{{ $persentase }}%</span>
            </div>

            <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden">
                <div
                    class="h-full bg-white rounded-full transition-all duration-500"
                    style="width: {{ $persentase }}%;">
                </div>
            </div>

        </div>

    </div>
</div>


{{-- SUMMARY (CARD LIST STYLE - COMPACT SAAS) --}}
<div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm mb-5">

    {{-- HEADER (lebih compact) --}}
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/30">
        <div class="text-sm font-semibold text-slate-900">
            Ringkasan Absensi
        </div>
        <div class="text-[11px] text-slate-500 mt-0.5">
            Distribusi kehadiran santri
        </div>
    </div>

    {{-- LIST --}}
    <div class="divide-y divide-slate-100">

        {{-- HADIR --}}
        <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50/40 transition">
            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-emerald-50 flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                </div>

                <div>
                    <div class="text-sm font-medium text-slate-900 leading-tight">Hadir</div>
                    <div class="text-[11px] text-slate-500">Kehadiran aktif</div>
                </div>

            </div>

            <div class="text-base font-semibold text-slate-900">
                {{ $hadir }}
            </div>
        </div>

        {{-- IZIN --}}
        <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50/40 transition">
            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-amber-50 flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                </div>

                <div>
                    <div class="text-sm font-medium text-slate-900 leading-tight">Izin</div>
                    <div class="text-[11px] text-slate-500">Izin resmi</div>
                </div>

            </div>

            <div class="text-base font-semibold text-slate-900">
                {{ $izin }}
            </div>
        </div>

        {{-- SAKIT --}}
        <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50/40 transition">
            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-sky-50 flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-sky-500"></div>
                </div>

                <div>
                    <div class="text-sm font-medium text-slate-900 leading-tight">Sakit</div>
                    <div class="text-[11px] text-slate-500">Tidak hadir karena sakit</div>
                </div>

            </div>

            <div class="text-base font-semibold text-slate-900">
                {{ $sakit }}
            </div>
        </div>

        {{-- TERLAMBAT --}}
        <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50/40 transition">
            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-indigo-50 flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                </div>

                <div>
                    <div class="text-sm font-medium text-slate-900 leading-tight">Terlambat</div>
                    <div class="text-[11px] text-slate-500">Keterlambatan hadir</div>
                </div>

            </div>

            <div class="text-base font-semibold text-slate-900">
                {{ $terlambat }}
            </div>
        </div>

        {{-- ALPHA --}}
        <div class="flex items-center justify-between px-5 py-3 hover:bg-slate-50/40 transition">
            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-rose-50 flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                </div>

                <div>
                    <div class="text-sm font-medium text-slate-900 leading-tight">Alpha</div>
                    <div class="text-[11px] text-slate-500">Tanpa keterangan</div>
                </div>

            </div>

            <div class="text-base font-semibold text-slate-900">
                {{ $alpha }}
            </div>
        </div>

    </div>
</div>

{{-- ABSENSI SEKOLAH --}}

@php
$absensiHarian = $siswa->absensiHarians;
@endphp

<div
    x-data="{ showAllHarian:false }"
    class="
        bg-white
        rounded-[30px]
        border border-slate-200
        overflow-hidden
        shadow-sm
    ">

{{-- HEADER --}}
<div class="px-4 py-3 border-b border-slate-100">

    <div class="flex items-center justify-between">

        <div>

            <h3 class="font-bold text-slate-900">
                Absensi Sekolah
            </h3>

            <p class="text-xs text-slate-500 mt-1">
                Riwayat absen masuk & pulang sekolah
            </p>

        </div>

        <div class="
                inline-flex
                items-center
                gap-2
                px-2.5 py-1.5
                rounded-2xl
                bg-slate-50
                text-slate-500
                text-xs
            ">

            <x-heroicon-o-finger-print class="w-4 h-4" />

            {{ $absensiHarian->count() }}
            Hari

        </div>

    </div>

</div>

{{-- LIST --}}
@forelse($absensiHarian as $index => $item)

    <div
        x-show="showAllHarian || {{ $index }} < 5"
        x-transition.duration.200ms
        class="
            px-4 py-3
            hover:bg-slate-50/80
            transition
            {{ !$loop->last ? 'border-b border-slate-100' : '' }}
        ">

        <div class="flex items-center justify-between">

            <div class="flex items-center gap-3">

                <div class="
                        w-10 h-10
                        rounded-xl
                        bg-[#00A39D]/10
                        flex items-center justify-center
                        shrink-0
                    ">

                    <x-heroicon-o-finger-print class="w-5 h-5 text-[#00A39D]" />

                </div>

                <div>

                    <div class="font-semibold text-sm text-slate-900">
                        {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->translatedFormat('d F Y') }}
                    </div>

                    <div class="text-xs text-slate-500 mt-0.5">
                        Masuk: {{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '-' }}
                        &nbsp;•&nbsp;
                        Pulang: {{ $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') : '-' }}
                    </div>

                </div>

            </div>

            <div class="flex flex-col items-end gap-1">

                @php
                    $warnaMasuk = match($item->status_masuk) {
                        'Hadir' => 'bg-emerald-50 text-emerald-600',
                        'Terlambat' => 'bg-indigo-50 text-indigo-600',
                        'Izin' => 'bg-amber-50 text-amber-600',
                        'Sakit' => 'bg-sky-50 text-sky-600',
                        'Alpa' => 'bg-rose-50 text-rose-600',
                        default => 'bg-slate-100 text-slate-500',
                    };
                @endphp

                @if($item->status_masuk)
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $warnaMasuk }}">
                        {{ $item->status_masuk }}
                    </span>
                @endif

                @if($item->status_pulang)
                    <span class="
                        text-[10px] font-semibold px-2 py-0.5 rounded-full
                        {{ $item->status_pulang === 'Pulang Awal' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}
                    ">
                        {{ $item->status_pulang }}
                    </span>
                @endif

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
                    bg-[#00A39D]/10
                    mx-auto
                    flex items-center justify-center
                ">

                <x-heroicon-o-finger-print
                    class="
                        w-8 h-8
                        text-[#00A39D]
                    " />

            </div>

            <div
                class="
                    font-bold
                    text-slate-900
                    mt-4
                ">

                Belum Ada Absensi

            </div>

            <div
                class="
                    text-sm
                    text-slate-500
                    mt-2
                ">

                Data absensi sekolah belum tersedia.

            </div>

        </div>

    </div>

@endforelse

@if($absensiHarian->count() > 5)

    <div class="px-4 py-3 border-t border-slate-100">

        <button
            @click="showAllHarian = !showAllHarian"
            class="
                w-full
                text-center
                text-xs
                font-semibold
                text-[#00A39D]
                py-1.5
            ">

            <span x-show="!showAllHarian">
                Lihat Semua Absensi Sekolah
            </span>

            <span x-show="showAllHarian">
                Tampilkan Lebih Sedikit
            </span>

        </button>

    </div>

@endif

</div>

{{-- ABSENSI MAPEL --}}

@php
$absensiMapel = $siswa->absensiMapels
->sortByDesc('tanggal')
->values();
@endphp

<div
    x-data="{ showAllMapel:false }"
    class="
        bg-white
        rounded-[30px]
        border border-slate-200
        overflow-hidden
        shadow-sm
        mt-6
    ">

{{-- HEADER --}}
<div class="px-4 py-3 border-b border-slate-100">

    <div class="flex items-center justify-between">

        <div>

            <h3
                class="
                    font-bold
                    text-slate-900
                ">

                Absensi Mata Pelajaran

            </h3>

            <p
                class="
                    text-xs
                    text-slate-500
                    mt-1
                ">

                Kehadiran saat pembelajaran

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

            <x-heroicon-o-academic-cap
                class="w-4 h-4" />

            {{ $absensiMapel->count() }}
            Absensi

        </div>

    </div>

</div>

{{-- LIST --}}
@forelse($absensiMapel as $index => $absensi)

    @php

        $statusColor = match($absensi->status){

            'Hadir'
                => 'bg-emerald-50 text-emerald-600',

            'Izin'
                => 'bg-amber-50 text-amber-600',

            'Sakit'
                => 'bg-sky-50 text-sky-600',

            default
                => 'bg-rose-50 text-rose-600',
        };

    @endphp

    <div
        x-show="showAllMapel || {{ $index }} < 5"
        x-transition.duration.200ms
        class="
            px-4 py-3
            hover:bg-slate-50/80
            transition
            {{ !$loop->last ? 'border-b border-slate-100' : '' }}
        ">

        <div class="flex items-center justify-between">

            <div class="flex items-center gap-3">

                <div
                    class="
                        w-10 h-10
                        rounded-xl
                        bg-indigo-50
                        flex items-center justify-center
                        shrink-0
                    ">

                    <x-heroicon-o-academic-cap
                        class="
                            w-5 h-5
                            text-indigo-600
                        " />

                </div>

                <div>

                    <div
                        class="
                            font-semibold
                            text-sm
                            text-slate-900
                        ">

                        {{
                            $absensi->jadwalPelajaran
                                ?->mataPelajaran
                                ?->nama
                            ?? 'Mata Pelajaran'
                        }}

                    </div>

                    <div
                        class="
                            text-sm
                            text-slate-500
                            mt-1
                        ">

                        Guru :
                        {{
                            $absensi->jadwalPelajaran
                                ?->guru
                                ?->nama
                            ?? '-'
                        }}

                    </div>

                    <div
                        class="
                            flex
                            items-center
                            gap-1
                            text-xs
                            text-slate-400
                            mt-1
                        ">

                        <x-heroicon-o-calendar
                            class="w-3 h-3" />

                        {{
                            \Carbon\Carbon::parse(
                                $absensi->tanggal
                            )->format('d M Y')
                        }}

                    </div>

                    @if($absensi->keterangan)

                        <div
                            class="
                                text-xs
                                text-slate-500
                                mt-2
                            ">

                            {{ $absensi->keterangan }}

                        </div>

                    @endif

                </div>

            </div>

            <div class="text-right">

                <span
                    class="
                        px-3 py-1
                        rounded-xl
                        text-xs
                        font-semibold
                        {{ $statusColor }}
                    ">

                    {{ $absensi->status }}

                </span>

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
                    bg-indigo-50
                    mx-auto
                    flex items-center justify-center
                ">

                <x-heroicon-o-academic-cap
                    class="
                        w-8 h-8
                        text-indigo-500
                    " />

            </div>

            <div
                class="
                    font-bold
                    text-slate-900
                    mt-4
                ">

                Belum Ada Absensi Pelajaran

            </div>

            <div
                class="
                    text-sm
                    text-slate-500
                    mt-2
                ">

                Data absensi pembelajaran belum tersedia.

            </div>

        </div>

    </div>

@endforelse

@if($absensiMapel->count() > 5)

    <div
        class="
            p-4
            border-t border-slate-100
            bg-slate-50/50
        ">

        <button
            x-on:click="
                showAllMapel =
                !showAllMapel
            "
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

            <span x-show="!showAllMapel">
                Lihat Semua Absensi Pelajaran
            </span>

            <span x-show="showAllMapel">
                Tampilkan Lebih Sedikit
            </span>

        </button>

    </div>

@endif

</div>

{{-- ABSENSI KEGIATAN --}}

@php
$absensiKegiatan = $siswa->absensis
->sortByDesc('waktu')
->values();
@endphp

<div
    x-data="{ showAllKegiatan:false }"
    class="
        bg-white
        rounded-[30px]
        border border-slate-200
        overflow-hidden
        shadow-sm
        mt-6
    ">

{{-- HEADER --}}
<div class="px-4 py-3 border-b border-slate-100">

    <div class="flex items-center justify-between">

        <div>

            <h3
                class="
                    font-bold
                    text-slate-900
                ">

                Absensi Kegiatan

            </h3>

            <p
                class="
                    text-xs
                    text-slate-500
                    mt-1
                ">

                Kehadiran kegiatan pondok

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

            {{ $absensiKegiatan->count() }}
            Absensi

        </div>

    </div>

</div>

{{-- LIST --}}
@forelse($absensiKegiatan as $index => $absensi)

    @php

        $statusColor = match($absensi->status){

            'Hadir'
                => 'bg-emerald-50 text-emerald-600',

            'Izin'
                => 'bg-amber-50 text-amber-600',

            'Sakit'
                => 'bg-sky-50 text-sky-600',

            default
                => 'bg-rose-50 text-rose-600',
        };

    @endphp

    <div
        x-show="showAllKegiatan || {{ $index }} < 5"
        x-transition.duration.200ms
        class="
            px-4 py-3
            hover:bg-slate-50/80
            transition
            {{ !$loop->last ? 'border-b border-slate-100' : '' }}
        ">

        <div class="flex items-center justify-between">

            <div class="flex items-center gap-3">

                <div
                    class="
                        w-10 h-10
                        rounded-xl
                        bg-[#00A39D]/10
                        flex items-center justify-center
                        shrink-0
                    ">

                    <x-heroicon-o-check-badge
                        class="
                            w-5 h-5
                            text-[#00A39D]
                        " />

                </div>

                <div>

                    <div
                        class="
                            font-semibold
                            text-sm
                            text-slate-900
                        ">

                        {{
                            $absensi->jadwalKegiatan
                                ?->template
                                ?->nama
                            ?? $absensi->tipe
                            ?? 'Kegiatan Pondok'
                        }}

                    </div>
                    <div
                        class="
                            text-sm
                            text-slate-500
                            mt-1
                        ">
                        Metode Absensi :
                        {{ $absensi->metode ?? 'Manual' }}

                    </div>

                    <div
                        class="
                            flex
                            items-center
                            gap-1
                            text-xs
                            text-slate-400
                            mt-1
                        ">

                        <x-heroicon-o-calendar
                            class="w-3 h-3" />

                        {{
                            \Carbon\Carbon::parse(
                                $absensi->waktu
                            )->format('d M Y H:i')
                        }}

                    </div>

                </div>

            </div>

            <div class="text-right">

                <span
                    class="
                        px-3 py-1
                        rounded-xl
                        text-xs
                        font-semibold
                        {{ $statusColor }}
                    ">

                    {{ $absensi->status }}

                </span>

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
                    bg-emerald-50
                    mx-auto
                    flex items-center justify-center
                ">

                <x-heroicon-o-check-circle
                    class="
                        w-8 h-8
                        text-emerald-500
                    " />

            </div>

            <div
                class="
                    font-bold
                    text-slate-900
                    mt-4
                ">

                Belum Ada Absensi

            </div>

            <div
                class="
                    text-sm
                    text-slate-500
                    mt-2
                ">

                Data absensi kegiatan belum tersedia.

            </div>

        </div>

    </div>

@endforelse

@if($absensiKegiatan->count() > 5)

    <div
        class="
            p-4
            border-t border-slate-100
            bg-slate-50/50
        ">

        <button
            x-on:click="
                showAllKegiatan =
                !showAllKegiatan
            "
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

            <span x-show="!showAllKegiatan">
                Lihat Semua Absensi Kegiatan
            </span>

            <span x-show="showAllKegiatan">
                Tampilkan Lebih Sedikit
            </span>

        </button>

    </div>

@endif

</div>

</div>

@endsection

