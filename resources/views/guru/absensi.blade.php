@extends('guru.layout.guru')

@section('content')

<div
    class="p-4 bg-slate-50 min-h-screen"
    x-data="{ tab:'all' }">

@php

    // Ringkasan Absensi Kegiatan (rapat, sholat berjamaah, dll -- dari
    // Absensi, BUKAN basis Tingkat Kehadiran lagi, cuma insight
    // tambahan yang terpisah)
    $hadir = $absensis->where('status','Hadir')->count();
    $izin = $absensis->where('status','Izin')->count();
    $sakit = $absensis->where('status','Sakit')->count();
    $terlambat = $absensis->where('status','Terlambat')->count();
    $alpa = $absensis->whereIn('status', ['Alpa', 'Alpha'])->count();
    $totalKegiatan = $absensis->count();

    // Ringkasan Absensi Harian (dari AbsensiHarian -- basis Tingkat
    // Kehadiran di hero)
    $harianHadir = $absensiHarians->where('status_masuk', 'Hadir')->count();
    $harianTerlambat = $absensiHarians->where('status_masuk', 'Terlambat')->count();
    $harianIzin = $absensiHarians->where('status_masuk', 'Izin')->count();
    $harianSakit = $absensiHarians->where('status_masuk', 'Sakit')->count();
    $harianAlpa = $absensiHarians->whereIn('status_masuk', ['Alpa', 'Alpha'])->count();

    $namaBulan = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');

@endphp

{{-- ================= HERO ================= --}}
<div
    class="relative overflow-hidden rounded-3xl
           bg-gradient-to-br
           from-[#00A39D]
           via-[#00B4AC]
           to-[#14C8C0]
           p-6
           text-white
           shadow-lg
           mb-5">

    <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/10 rounded-full"></div>
    <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-white/5 rounded-full"></div>

    <div class="relative z-10">

        <div class="text-white/80 text-sm">
            Teacher Attendance
        </div>

        <h1 class="text-2xl font-bold mt-1">
            Absensi Guru
        </h1>

        <p class="text-white/80 text-sm mt-1">
            Rekapitulasi absen masuk & pulang sekolah
        </p>

        <div class="mt-6">

            <div class="flex justify-between text-sm mb-2">

                <span class="text-white/80">
                    Tingkat Kehadiran
                </span>

                <span class="font-semibold">
                    {{ $persentaseKehadiran }}%
                </span>

            </div>

            <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden">

                <div
                    class="h-full bg-white rounded-full transition-all duration-500"
                    style="width:{{ $persentaseKehadiran }}%">

                </div>

            </div>

            <p class="text-white/70 text-xs mt-2">
                {{ $hariHadir }} dari {{ $totalHariTercatat }} hari tercatat masuk sekolah bulan {{ $namaBulan }}
            </p>

        </div>

    </div>

</div>

{{-- PILIH PERIODE --}}
<div class="flex items-center justify-between mb-4">

    <a
        href="{{ route('guru.absensi', ['bulan' => $bulan == 1 ? 12 : $bulan - 1, 'tahun' => $bulan == 1 ? $tahun - 1 : $tahun]) }}"
        class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50">
        <x-heroicon-o-chevron-left class="w-4 h-4"/>
    </a>

    <div class="font-semibold text-slate-900 text-sm">
        {{ $namaBulan }} {{ $tahun }}
    </div>

    @php
        $bulanDepan = $bulan == 12 ? 1 : $bulan + 1;
        $tahunDepan = $bulan == 12 ? $tahun + 1 : $tahun;
        $bukanBulanDepan = \Carbon\Carbon::create($tahunDepan, $bulanDepan, 1)->startOfMonth()->gt(now()->startOfMonth());
    @endphp

    <a
        href="{{ $bukanBulanDepan ? '#' : route('guru.absensi', ['bulan' => $bulanDepan, 'tahun' => $tahunDepan]) }}"
        class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center {{ $bukanBulanDepan ? 'text-slate-200 pointer-events-none' : 'text-slate-500 hover:bg-slate-50' }}">
        <x-heroicon-o-chevron-right class="w-4 h-4"/>
    </a>

</div>


{{-- DIVIDER ANTAR KELOMPOK --}}
<div class="flex items-center gap-3 my-2 px-1">
    <div class="flex-1 h-px bg-slate-200"></div>
    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Absensi Harian</span>
    <div class="flex-1 h-px bg-slate-200"></div>
</div>

{{-- RINGKASAN ABSENSI HARIAN --}}
@include('wali.partials.ringkasan-absensi-card', [
    'judul' => 'Ringkasan Absensi Harian',
    'subjudul' => 'Basis Tingkat Kehadiran di atas -- ' . $totalHariTercatat . ' hari tercatat bulan ' . $namaBulan,
    'rows' => [
        ['label' => 'Hadir', 'keterangan' => 'Kehadiran tepat waktu', 'color' => 'emerald', 'value' => $harianHadir],
        ['label' => 'Terlambat', 'keterangan' => 'Datang terlambat', 'color' => 'indigo', 'value' => $harianTerlambat],
        ['label' => 'Izin', 'keterangan' => 'Izin resmi', 'color' => 'amber', 'value' => $harianIzin],
        ['label' => 'Sakit', 'keterangan' => 'Tidak hadir karena sakit', 'color' => 'sky', 'value' => $harianSakit],
        ['label' => 'Alpa', 'keterangan' => 'Tanpa keterangan', 'color' => 'rose', 'value' => $harianAlpa],
    ],
])

{{-- ================= ABSENSI MASUK & PULANG ================= --}}
@php
    $riwayatHarian = $pegawai->absensiHarians;
@endphp

<div
    x-data="{ showAllHarian:false }"
    class="bg-white
           border
           border-slate-200
           rounded-3xl
           overflow-hidden
           shadow-sm
           mb-6">

    <div class="px-4 py-3 border-b border-slate-100">

        <div class="flex items-center justify-between">

            <div>
                <h3 class="font-bold text-slate-900">
                    Absensi Masuk & Pulang
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
                {{ $riwayatHarian->count() }}
                Hari
            </div>

        </div>

    </div>

    @forelse($riwayatHarian as $index => $item)

        <div
            x-show="showAllHarian || {{ $index }} < 5"
            x-transition.duration.200ms
            class="px-4 py-3 hover:bg-slate-50/80 transition
                {{ !$loop->last ? 'border-b border-slate-100' : '' }}">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">

                    <div class="w-10 h-10 rounded-xl bg-[#00A39D]/10 flex items-center justify-center shrink-0">
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
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full
                            {{ $item->status_pulang === 'Pulang Awal' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}">
                            {{ $item->status_pulang }}
                        </span>
                    @endif

                </div>

            </div>

        </div>

    @empty

        <div class="px-4 py-8 text-center text-sm text-slate-400">
            Belum ada riwayat absensi masuk & pulang.
        </div>

    @endforelse

    @if($riwayatHarian->count() > 5)

        <div class="px-4 py-3 border-t border-slate-100">

            <button
                @click="showAllHarian = !showAllHarian"
                class="w-full text-center py-2.5
                    rounded-2xl
                    bg-[#00A39D]/10
                    hover:bg-[#00A39D]/20
                    text-[#00A39D]
                    font-medium
                    text-sm
                    transition">

                <span x-show="!showAllHarian">
                    Lihat Semua Absensi Masuk & Pulang
                </span>

                <span x-show="showAllHarian">
                    Tampilkan Lebih Sedikit
                </span>

            </button>

        </div>

    @endif

</div>


{{-- DIVIDER ANTAR KELOMPOK --}}
<div class="flex items-center gap-3 my-2 px-1">
    <div class="flex-1 h-px bg-slate-200"></div>
    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Absensi Kegiatan</span>
    <div class="flex-1 h-px bg-slate-200"></div>
</div>

{{-- ================= RINGKASAN ================= --}}
<div
    class="bg-white
           border
           border-slate-200
           rounded-3xl
           overflow-hidden
           shadow-sm
           mb-6">

    <div
        class="px-5 py-3
               border-b
               border-slate-100
               bg-slate-50">

        <div class="text-base font-semibold text-slate-900">
            Ringkasan Absensi Kegiatan
        </div>

        <div class="text-[13px] text-slate-500 mt-1">
            {{ $totalKegiatan }} kegiatan tercatat bulan {{ $namaBulan }} (rapat, sholat berjamaah, dll)
        </div>

    </div>

    <div class="divide-y divide-slate-100">

        {{-- HADIR --}}
        <div class="flex items-center justify-between px-5 py-3">

            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-emerald-50 flex items-center justify-center">

                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>

                </div>

                <div>

                    <div class="font-medium text-sm">
                        Hadir
                    </div>

                    <div class="text-xs text-slate-500">
                        Kehadiran tepat waktu
                    </div>

                </div>

            </div>

            <div class="font-semibold text-base">
                {{ $hadir }}
            </div>

        </div>

        {{-- IZIN --}}
        <div class="flex items-center justify-between px-5 py-3">

            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-amber-50 flex items-center justify-center">

                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>

                </div>

                <div>

                    <div class="font-medium text-sm">
                        Izin
                    </div>

                    <div class="text-xs text-slate-500">
                        Izin resmi
                    </div>

                </div>

            </div>

            <div class="font-semibold text-base">
                {{ $izin }}
            </div>

        </div>

        {{-- SAKIT --}}
        <div class="flex items-center justify-between px-5 py-3">

            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-sky-50 flex items-center justify-center">

                    <div class="w-2 h-2 rounded-full bg-sky-500"></div>

                </div>

                <div>

                    <div class="font-medium text-sm">
                        Sakit
                    </div>

                    <div class="text-xs text-slate-500">
                        Tidak hadir karena sakit
                    </div>

                </div>

            </div>

            <div class="font-semibold text-base">
                {{ $sakit }}
            </div>

        </div>

        {{-- TERLAMBAT --}}
        <div class="flex items-center justify-between px-5 py-3">

            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-indigo-50 flex items-center justify-center">

                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div>

                </div>

                <div>

                    <div class="font-medium text-sm">
                        Terlambat
                    </div>

                    <div class="text-xs text-slate-500">
                        Datang terlambat
                    </div>

                </div>

            </div>

            <div class="font-semibold text-base">
                {{ $terlambat }}
            </div>

        </div>

        {{-- ALPA --}}
        <div class="flex items-center justify-between px-5 py-3">

            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-2xl bg-rose-50 flex items-center justify-center">

                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>

                </div>

                <div>

                    <div class="font-medium text-sm">
                        Alpa
                    </div>

                    <div class="text-xs text-slate-500">
                        Tanpa keterangan
                    </div>

                </div>

            </div>

            <div class="font-semibold text-base">
                {{ $alpa }}
            </div>

        </div>

    </div>

</div>

{{-- ================= RIWAYAT ABSENSI GURU ================= --}}
@php
    $riwayatAbsensi = $absensis
        ->sortByDesc('waktu')
        ->values();
@endphp

<div
    x-data="{ showAllAbsensi:false }"
    class="bg-white
           border
           border-slate-200
           rounded-3xl
           overflow-hidden
           shadow-sm
           mb-6">

    {{-- HEADER --}}
    <div
        class="px-5 py-3
               border-b
               border-slate-100
               bg-slate-50">

        <div class="flex items-center justify-between">

            <div>

                <div class="text-base font-semibold text-slate-900">
                    Riwayat Absensi Guru
                </div>

                <div class="text-[13px] text-slate-500 mt-1">
                    Riwayat kehadiran guru
                </div>

            </div>

            <div
                class="px-3 py-1
                       rounded-xl
                       bg-white
                       border border-slate-200
                       text-xs
                       text-slate-600">

                {{ $riwayatAbsensi->count() }} Data

            </div>

        </div>

    </div>

    {{-- LIST --}}
    @forelse($riwayatAbsensi as $index => $absensi)

        @php

            $statusColor = match($absensi->status){

                'Hadir'
                    => 'bg-emerald-50 text-emerald-600',

                'Izin'
                    => 'bg-amber-50 text-amber-600',

                'Sakit'
                    => 'bg-sky-50 text-sky-600',

                'Terlambat'
                    => 'bg-indigo-50 text-indigo-600',

                default
                    => 'bg-rose-50 text-rose-600',

            };

        @endphp

        <div
            x-show="showAllAbsensi || {{ $index }} < 5"
            x-transition.duration.200ms
            class="
                px-4 py-3
                hover:bg-slate-50
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

                        <x-heroicon-o-check-circle
                            class="w-5 h-5 text-[#00A39D]" />

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
                                ?? ucfirst($absensi->tipe ?? 'Absensi Guru')
                            }}

                        </div>

                        <div
                            class="
                                text-sm
                                text-slate-500
                                mt-1
                            ">

                            Metode :
                            {{ $absensi->metode ?? 'Manual' }}

                        </div>

                        <div
                            class="
                                text-xs
                                text-slate-400
                                mt-1
                                flex
                                items-center
                                gap-1
                            ">

                            <x-heroicon-o-clock
                                class="w-3 h-3"/>

                            {{
                                \Carbon\Carbon::parse($absensi->waktu)
                                    ->translatedFormat('d M Y • H:i')
                            }}

                        </div>

                    </div>

                </div>

                <div>

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

                    <x-heroicon-o-calendar
                        class="w-8 h-8 text-emerald-500"/>

                </div>

                <div
                    class="
                        font-bold
                        text-slate-900
                        mt-4
                    ">

                    Belum Ada Riwayat Absensi

                </div>

                <div
                    class="
                        text-sm
                        text-slate-500
                        mt-2
                    ">

                    Riwayat kehadiran guru akan muncul di sini.

                </div>

            </div>

        </div>

    @endforelse

    @if($riwayatAbsensi->count() > 5)

        <div
            class="
                p-4
                border-t
                border-slate-100
                bg-slate-50/50
            ">

            <button
                x-on:click="
                    showAllAbsensi = !showAllAbsensi
                "
                class="
                    w-full
                    py-3
                    rounded-2xl
                    bg-[#00A39D]/10
                    hover:bg-[#00A39D]/20
                    text-[#00A39D]
                    font-medium
                    text-sm
                    transition
                ">

                <span x-show="!showAllAbsensi">
                    Lihat Semua Riwayat Absensi
                </span>

                <span x-show="showAllAbsensi">
                    Tampilkan Lebih Sedikit
                </span>

            </button>

        </div>

    @endif

</div>


{{-- DIVIDER ANTAR KELOMPOK --}}
<div class="flex items-center gap-3 my-2 px-1">
    <div class="flex-1 h-px bg-slate-200"></div>
    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Jurnal Mengajar</span>
    <div class="flex-1 h-px bg-slate-200"></div>
</div>

{{-- ================= RIWAYAT JURNAL MENGAJAR ================= --}}
@php
    $riwayatJurnal = $jurnals
        ->sortByDesc('tanggal')
        ->values();
@endphp

<div
    x-data="{ showAllJurnal:false }"
    class="bg-white
           border
           border-slate-200
           rounded-3xl
           overflow-hidden
           shadow-sm
           mb-6">

    {{-- HEADER --}}
    <div
        class="px-5 py-3
               border-b
               border-slate-100
               bg-slate-50">

        <div class="flex items-center justify-between">

            <div>

                <div class="text-base font-semibold text-slate-900">
                    Riwayat Jurnal Mengajar
                </div>

                <div class="text-[13px] text-slate-500 mt-1">
                    Aktivitas pembelajaran yang diinput
                </div>

            </div>

            <div
                class="px-3 py-1
                       rounded-xl
                       bg-white
                       border border-slate-200
                       text-xs
                       text-slate-600">

                {{ $riwayatJurnal->count() }} Jurnal

            </div>

        </div>

    </div>

    {{-- LIST --}}
    @forelse($riwayatJurnal as $index => $jurnal)

        @php

            $statusColor = match(strtolower($jurnal->status ?? '')){

                'selesai','approved','valid'
                    => 'bg-emerald-50 text-emerald-600',

                'pending'
                    => 'bg-amber-50 text-amber-600',

                'ditolak','rejected'
                    => 'bg-rose-50 text-rose-600',

                default
                    => 'bg-slate-100 text-slate-600',
            };

        @endphp

        <div
            x-show="showAllJurnal || {{ $index }} < 5"
            x-transition.duration.200ms
            class="
                px-4 py-3
                hover:bg-slate-50
                transition
                {{ !$loop->last ? 'border-b border-slate-100' : '' }}
            ">

            <div class="flex items-start justify-between">

                <div class="flex gap-3">

                    <div
                        class="
                            w-10 h-10
                            rounded-xl
                            bg-indigo-50
                            flex
                            items-center
                            justify-center
                            shrink-0
                        ">

                        <x-heroicon-o-academic-cap
                            class="w-5 h-5 text-indigo-600"/>

                    </div>

                    <div>

                        {{-- Mata Pelajaran --}}
                        <div class="font-semibold text-sm text-slate-900">

                            {{ $jurnal->mataPelajaran->nama ?? '-' }}

                        </div>

                        {{-- Kelas + Jam --}}
                        <div class="text-sm text-slate-500 mt-1">

                            {{ $jurnal->kelas->nama ?? '-' }}

                            @if($jurnal->jamPelajaran)

                                •
                                {{ \Carbon\Carbon::parse($jurnal->jamPelajaran->jam_mulai)->format('H:i') }}
                                -
                                {{ \Carbon\Carbon::parse($jurnal->jamPelajaran->jam_selesai)->format('H:i') }}
                            
                            @endif

                        </div>

                        {{-- Materi --}}
                        <div
                            class="
                                text-sm
                                text-slate-700
                                mt-2
                                line-clamp-2
                            ">

                            {{ $jurnal->materi }}

                        </div>

                        {{-- Tanggal --}}
                        <div
                            class="
                                flex
                                items-center
                                gap-1
                                text-xs
                                text-slate-400
                                mt-2
                            ">

                            <x-heroicon-o-calendar
                                class="w-3 h-3"/>

                            {{
                                \Carbon\Carbon::parse($jurnal->tanggal)
                                    ->translatedFormat('d F Y')
                            }}

                        </div>

                    </div>

                </div>

                <div>

                    <span
                        class="
                            px-3
                            py-1
                            rounded-xl
                            text-xs
                            font-semibold
                            {{ $statusColor }}
                        ">

                        {{ ucfirst($jurnal->status ?? 'Draft') }}

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
                        flex
                        items-center
                        justify-center
                    ">

                    <x-heroicon-o-book-open
                        class="w-8 h-8 text-indigo-500"/>

                </div>

                <div
                    class="
                        font-bold
                        text-slate-900
                        mt-4
                    ">

                    Belum Ada Jurnal Mengajar

                </div>

                <div
                    class="
                        text-sm
                        text-slate-500
                        mt-2
                    ">

                    Riwayat jurnal mengajar akan tampil di sini.

                </div>

            </div>

        </div>

    @endforelse

    @if($riwayatJurnal->count() > 5)

        <div
            class="
                p-4
                border-t border-slate-100
                bg-slate-50/50
            ">

            <button
                x-on:click="
                    showAllJurnal =
                    !showAllJurnal
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

                <span x-show="!showAllJurnal">
                    Lihat Semua Jurnal
                </span>

                <span x-show="showAllJurnal">
                    Tampilkan Lebih Sedikit
                </span>

            </button>

        </div>

    @endif

</div>

</div>

@endsection