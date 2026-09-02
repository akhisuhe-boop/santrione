@extends('wali.layout.wali')

@section('content')

<div
    class="p-4 bg-slate-50 min-h-screen"
    x-data="{ tab:'all' }">

    @php
    // ===============================
    // RINGKASAN ABSENSI HARIAN (dari AbsensiHarian -- basis Tingkat
    // Kehadiran di hero). Terpisah dari mapel & kegiatan di bawah.
    // ===============================
    $harianHadir = $siswa->absensiHarians->where('status_masuk', 'Hadir')->count();
    $harianTerlambat = $siswa->absensiHarians->where('status_masuk', 'Terlambat')->count();
    $harianIzin = $siswa->absensiHarians->where('status_masuk', 'Izin')->count();
    $harianSakit = $siswa->absensiHarians->where('status_masuk', 'Sakit')->count();
    $harianAlpa = $siswa->absensiHarians->whereIn('status_masuk', ['Alpa', 'Alpha'])->count();

    // ===============================
    // RINGKASAN ABSENSI MAPEL (dari AbsensiMapel saja -- terpisah dari
    // kegiatan, karena beda konteks: per sesi pelajaran, bukan kegiatan
    // pesantren)
    // ===============================
    $mapelHadir = $siswa->absensiMapels->where('status', 'Hadir')->count();
    $mapelIzin = $siswa->absensiMapels->where('status', 'Izin')->count();
    $mapelSakit = $siswa->absensiMapels->where('status', 'Sakit')->count();
    $mapelAlpha = $siswa->absensiMapels->whereIn('status', ['Alpa', 'Alpha'])->count();
    $totalMapel = $siswa->absensiMapels->count();

    // ===============================
    // RINGKASAN ABSENSI KEGIATAN (dari Absensi saja -- sholat
    // berjamaah, ekskul, kegiatan pesantren lainnya)
    // ===============================
    $kegiatanHadir = $siswa->absensis->where('status', 'Hadir')->count();
    $kegiatanIzin = $siswa->absensis->where('status', 'Izin')->count();
    $kegiatanSakit = $siswa->absensis->where('status', 'Sakit')->count();
    $kegiatanAlpa = $siswa->absensis->whereIn('status', ['Alpa', 'Alpha'])->count();
    $totalKegiatan = $siswa->absensis->count();

    $namaBulan = \Carbon\Carbon::create()->month($bulan)->translatedFormat('F');
    @endphp

    {{-- PILIH PERIODE --}}
    <div class="flex items-center justify-between mb-4">

        <a
            href="{{ route('wali.absensi', ['bulan' => $bulan == 1 ? 12 : $bulan - 1, 'tahun' => $bulan == 1 ? $tahun - 1 : $tahun]) }}"
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
            href="{{ $bukanBulanDepan ? '#' : route('wali.absensi', ['bulan' => $bulanDepan, 'tahun' => $tahunDepan]) }}"
            class="w-9 h-9 rounded-xl bg-white border border-slate-200 flex items-center justify-center {{ $bukanBulanDepan ? 'text-slate-200 pointer-events-none' : 'text-slate-500 hover:bg-slate-50' }}">
            <x-heroicon-o-chevron-right class="w-4 h-4"/>
        </a>

    </div>

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
            Rekapitulasi absen masuk & pulang sekolah
        </p>

        {{-- PROGRESS --}}
        <div class="mt-6">

            <div class="flex justify-between text-sm mb-2">
                <span class="text-white/80">Tingkat Kehadiran</span>
                <span class="font-semibold">{{ $persentaseKehadiran }}%</span>
            </div>

            <div class="w-full h-2 bg-white/20 rounded-full overflow-hidden">
                <div
                    class="h-full bg-white rounded-full transition-all duration-500"
                    style="width: {{ $persentaseKehadiran }}%;">
                </div>
            </div>

            <p class="text-white/70 text-xs mt-2">
                {{ $hariHadir }} dari {{ $totalHariTercatat }} hari tercatat masuk sekolah bulan {{ $namaBulan }}
            </p>

        </div>

    </div>
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
        ['label' => 'Hadir', 'keterangan' => 'Kehadiran aktif', 'color' => 'emerald', 'value' => $harianHadir],
        ['label' => 'Terlambat', 'keterangan' => 'Keterlambatan hadir', 'color' => 'indigo', 'value' => $harianTerlambat],
        ['label' => 'Izin', 'keterangan' => 'Izin resmi', 'color' => 'amber', 'value' => $harianIzin],
        ['label' => 'Sakit', 'keterangan' => 'Tidak hadir karena sakit', 'color' => 'sky', 'value' => $harianSakit],
        ['label' => 'Alpa', 'keterangan' => 'Tanpa keterangan', 'color' => 'rose', 'value' => $harianAlpa],
    ],
])

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
        mb-6
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

{{-- DIVIDER ANTAR KELOMPOK --}}
<div class="flex items-center gap-3 my-2 px-1">
    <div class="flex-1 h-px bg-slate-200"></div>
    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Absensi Mapel</span>
    <div class="flex-1 h-px bg-slate-200"></div>
</div>

{{-- RINGKASAN ABSENSI MAPEL --}}
@include('wali.partials.ringkasan-absensi-card', [
    'judul' => 'Ringkasan Absensi Mapel',
    'subjudul' => $totalMapel . ' sesi pelajaran tercatat bulan ' . $namaBulan . ' -- sesi yang tidak sempat diabsen guru tidak masuk hitungan ini',
    'rows' => [
        ['label' => 'Hadir', 'keterangan' => 'Hadir di kelas', 'color' => 'emerald', 'value' => $mapelHadir],
        ['label' => 'Izin', 'keterangan' => 'Izin resmi', 'color' => 'amber', 'value' => $mapelIzin],
        ['label' => 'Sakit', 'keterangan' => 'Tidak hadir karena sakit', 'color' => 'sky', 'value' => $mapelSakit],
        ['label' => 'Alpha', 'keterangan' => 'Tanpa keterangan', 'color' => 'rose', 'value' => $mapelAlpha],
    ],
])

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
        mb-6
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

{{-- DIVIDER ANTAR KELOMPOK --}}
<div class="flex items-center gap-3 my-2 px-1">
    <div class="flex-1 h-px bg-slate-200"></div>
    <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider">Absensi Kegiatan</span>
    <div class="flex-1 h-px bg-slate-200"></div>
</div>

{{-- RINGKASAN ABSENSI KEGIATAN --}}
@include('wali.partials.ringkasan-absensi-card', [
    'judul' => 'Ringkasan Absensi Kegiatan',
    'subjudul' => $totalKegiatan . ' kegiatan tercatat bulan ' . $namaBulan . ' (sholat berjamaah, ekskul, dll)',
    'rows' => [
        ['label' => 'Hadir', 'keterangan' => 'Ikut kegiatan', 'color' => 'emerald', 'value' => $kegiatanHadir],
        ['label' => 'Izin', 'keterangan' => 'Izin resmi', 'color' => 'amber', 'value' => $kegiatanIzin],
        ['label' => 'Sakit', 'keterangan' => 'Tidak hadir karena sakit', 'color' => 'sky', 'value' => $kegiatanSakit],
        ['label' => 'Alpa', 'keterangan' => 'Tanpa keterangan', 'color' => 'rose', 'value' => $kegiatanAlpa],
    ],
])

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

                Kehadiran kegiatan sekolah

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

