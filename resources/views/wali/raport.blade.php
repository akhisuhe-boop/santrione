@extends('wali.layout.wali')

@section('content')

<div class="p-4 space-y-5">

    {{-- ========================= --}}
    {{-- HERO RAPORT --}}
    {{-- ========================= --}}
    <div class="
        relative
        overflow-hidden
        rounded-[28px]

        bg-gradient-to-br
        from-[#00A39D]
        via-[#00B4AC]
        to-[#14C8C0]

        p-5
        text-white
        shadow-xl
    ">

        {{-- ORNAMENT --}}
        <div class="
            absolute top-0 right-0
            w-40 h-40
            rounded-full
            bg-white/10
            -mr-16 -mt-16
        "></div>

        <div class="
            absolute bottom-0 left-0
            w-28 h-28
            rounded-full
            bg-white/5
            -ml-10 -mb-10
        "></div>

        <div class="relative z-10">

            {{-- TITLE --}}
            <div class="text-sm text-white/80">
                Raport Digital
            </div>

            <div class="text-xl font-bold mt-1">
                {{ $raport->siswa->nama_lengkap ?? '-' }}
            </div>

            {{-- TAGS --}}
            <div class="flex flex-wrap gap-2 mt-3">

                <span class="
                    px-3 py-1
                    rounded-full
                    bg-white/15
                    border border-white/20
                    text-[11px]
                    font-medium
                    backdrop-blur-sm
                ">
                    Tahun : {{ $raport->tahunAjaran->nama ?? '-' }}
                </span>

                <span class="
                    px-3 py-1
                    rounded-full
                    bg-white/15
                    border border-white/20
                    text-[11px]
                    font-medium
                    backdrop-blur-sm
                ">
                    Semester : {{ $raport->semester ?? '-' }}
                </span>

            </div>

            {{-- DIVIDER --}}
            <div class="border-t border-white/20 my-4"></div>

            {{-- INFO GRID --}}
            <div class="grid grid-cols-3 gap-3">

                {{-- NIS --}}
                <div class="
                    rounded-2xl
                    bg-white/10
                    border border-white/10
                    backdrop-blur-sm
                    p-3
                ">
                    <div class="text-[11px] text-white/70 mb-1">
                        NIS
                    </div>
                    <div class="text-sm font-semibold">
                        {{ $raport->siswa->nis ?? '-' }}
                    </div>
                </div>

                {{-- NISN --}}
                <div class="
                    rounded-2xl
                    bg-white/10
                    border border-white/10
                    backdrop-blur-sm
                    p-3
                ">
                    <div class="text-[11px] text-white/70 mb-1">
                        NISN
                    </div>
                    <div class="text-sm font-semibold">
                        {{ $raport->siswa->nisn ?? '-' }}
                    </div>
                </div>

                {{-- KELAS --}}
                <div class="
                    rounded-2xl
                    bg-white/10
                    border border-white/10
                    backdrop-blur-sm
                    p-3
                ">
                    <div class="text-[11px] text-white/70 mb-1">
                        Kelas
                    </div>
                    <div class="text-sm font-semibold">
                        {{ $raport->kelas->nama ?? '-' }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    @if(!$raport)

        {{-- EMPTY --}}
        <x-wali.empty
            title="Raport Belum Tersedia"
            description="Data raport non akademik belum dipublish."
        />

    @else

    {{-- ========================= --}}
    {{-- RESUME AKADEMIK (SAAS STYLE) --}}
    {{-- ========================= --}}

    @php
        $avgNilai = $nilaiAkademik->avg('nilai_akhir');
        $maxNilai = $nilaiAkademik->max('nilai_akhir');
        $minNilai = $nilaiAkademik->min('nilai_akhir');
    @endphp

    <div class="grid grid-cols-3 gap-3">

        {{-- CARD 1 --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-50 via-white to-teal-50 border border-emerald-100 shadow-sm p-4">

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-emerald-600" />
                </div>

                <div>
                    <div class="text-xs text-slate-500">Rata-rata</div>
                    <div class="text-lg font-bold text-slate-900">
                        {{ number_format($avgNilai, 1) ?? '-' }}
                    </div>
                </div>
            </div>

        </div>

        {{-- CARD 2 --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sky-50 via-white to-blue-50 border border-sky-100 shadow-sm p-4">

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-sky-600" />
                </div>

                <div>
                    <div class="text-xs text-slate-500">Nilai Tertinggi</div>
                    <div class="text-lg font-bold text-slate-900">
                        {{ $maxNilai ?? '-' }}
                    </div>
                </div>
            </div>

        </div>

        {{-- CARD 3 --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-50 via-white to-amber-50 border border-orange-100 shadow-sm p-4">

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                    <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-orange-600" />
                </div>

                <div>
                    <div class="text-xs text-slate-500">Nilai Terendah</div>
                    <div class="text-lg font-bold text-slate-900">
                        {{ $minNilai ?? '-' }}
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- ========================= --}}
    {{-- AKADEMIK --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-[28px] border border-slate-100 shadow-sm overflow-hidden">

        {{-- HEADER --}}
        <div class="p-4 border-b border-slate-100 flex items-center gap-2 bg-emerald-50">
            <x-heroicon-o-book-open class="w-5 h-5 text-emerald-500" />
            <div class="font-bold text-slate-900">Nilai Akademik</div>
        </div>

        {{-- LIST --}}
        <div class="divide-y divide-slate-100">

            @forelse($nilaiAkademik as $nilai)

                <div class="px-4 py-3 flex items-center justify-between hover:bg-slate-50 transition">

                    <div class="flex items-center gap-3">

                        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <x-heroicon-o-book-open class="w-4 h-4 text-emerald-500" />
                        </div>

                        <div>
                            <div class="text-sm font-semibold text-slate-900">
                                {{ $nilai->mapel->nama ?? '-' }}
                            </div>

                            <div class="text-xs text-slate-400">
                                Guru : {{ $nilai->guru->nama ?? ($nilai->guru->name ?? '-') }}
                            </div>
                        </div>

                    </div>

                    <div class="text-right">
                        <div class="text-lg font-bold text-emerald-600">
                            {{ $nilai->nilai_akhir }}
                        </div>
                        <div class="text-xs text-slate-400">
                            {{ $nilai->grade }}
                        </div>
                    </div>

                </div>

            @empty
                <div class="p-5 text-sm text-slate-400">
                    Belum ada data rekap nilai
                </div>
            @endforelse

        </div>
    </div>

    {{-- ========================= --}}
    {{-- KEPRIBADIAN --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-[28px] border border-slate-100 shadow-sm overflow-hidden mt-4">

        {{-- HEADER --}}
        <div class="p-4 border-b border-slate-100 flex items-center gap-2 bg-teal-50">
            <x-heroicon-o-heart class="w-5 h-5 text-teal-500" />
            <div class="font-bold text-slate-900">Kepribadian</div>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse($raport->kepribadians ?? [] as $item)

                <div class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">
                            {{ $item->aspek }}
                        </div>

                        <div class="text-xs text-slate-400">
                            Nilai: {{ $item->nilai ?? '-' }}
                        </div>
                    </div>

                    <span class="text-sm font-semibold text-slate-700">
                        {{ $item->grade ?? '-' }}
                    </span>

                </div>

            @empty
                <div class="p-5 text-sm text-slate-400">
                    Belum ada data kepribadian
                </div>
            @endforelse

        </div>
    </div>

    {{-- ========================= --}}
    {{-- EKSTRAKURIKULER --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-[28px] border border-slate-100 shadow-sm overflow-hidden mt-4">

        {{-- HEADER --}}
        <div class="p-4 border-b border-slate-100 flex items-center gap-2 bg-orange-50">
            <x-heroicon-o-star class="w-5 h-5 text-orange-500" />
            <div class="font-bold text-slate-900">Ekstrakurikuler</div>
        </div>

        <div class="divide-y divide-slate-100">

            @forelse($raport->ekstrakurikulers ?? [] as $item)

                <div class="px-4 py-3 flex items-center justify-between">

                    <div>
                        <div class="text-sm font-semibold text-slate-900">
                            {{ $item->nama ?? $item->nama_ekskul ?? '-' }}
                        </div>

                        <div class="text-xs text-slate-400">
                            Nilai: {{ $item->nilai ?? '-' }}
                        </div>
                    </div>

                    <span class="text-sm font-semibold text-slate-700">
                        {{ $item->grade ?? '-' }}
                    </span>

                </div>

            @empty
                <div class="p-5 text-sm text-slate-400">
                    Belum ada data ekstrakurikuler
                </div>
            @endforelse

        </div>
    </div>

    {{-- ========================= --}}
    {{-- CATATAN WALI KELAS --}}
    {{-- ========================= --}}
    <div class="bg-white rounded-[28px] border border-slate-100 shadow-sm p-5 mt-4">

        <div class="flex items-center gap-2 mb-2">
            <x-heroicon-o-chat-bubble-left class="w-5 h-5 text-emerald-500" />
            <div class="font-bold text-slate-900">Catatan Wali Kelas</div>
        </div>

        <p class="text-sm text-slate-600 leading-relaxed">
            {{ $raport->catatan_wali_kelas ?? 'Tidak ada catatan.' }}
        </p>

    </div>

    @endif

</div>

@endsection