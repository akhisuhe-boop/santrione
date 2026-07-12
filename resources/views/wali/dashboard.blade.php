@extends('wali.layout.wali')

@section('content')

<div class="px-4 pb-4 bg-[#F8FAFB] min-h-screen">

    {{-- HEADER --}}
    <div class="-mx-4 mb-3 px-4 pt-2 pb-1">

         <div class="flex justify-between items-center px-3 py-2">

            <div>
                <div class="text-xs text-slate-500">
                    Assalamualaikum, Selamat Datang
                </div>

                <div class="font-bold text-base text-slate-900">
                    {{ session('wali_nama') }}
                </div>
            </div>

            <div class="text-right">
                <div class="text-xs text-slate-500">
                    {{ now()->translatedFormat('l') }}
                </div>

                <div class="text-sm font-semibold text-slate-900">
                    {{ now()->translatedFormat('d M Y') }}
                </div>
            </div>

    </div>

</div>

    {{-- CARD SANTRI --}}

    <div
        class="
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
    <div
        class="
            absolute
            top-0
            right-0
            w-40
            h-40
            rounded-full
            bg-white/10
            -mr-16
            -mt-16
        ">
    </div>

    <div
        class="
            absolute
            bottom-0
            left-0
            w-28
            h-28
            rounded-full
            bg-white/5
            -ml-10
            -mb-10
        ">
    </div>

    <div class="relative z-10">

        <div class="flex gap-4">

            {{-- AVATAR --}}
            <div class="relative">

                <div
                    class="
                        w-[72px] h-[72px]
                        rounded-2xl
                        overflow-hidden
                        bg-white/20
                        backdrop-blur-sm
                        border border-white/20
                        shadow-lg
                    "
                >

                    @if($siswa->foto)

                        <img
                            src="{{ asset('storage/' . $siswa->foto) }}"
                            alt="{{ $siswa->nama_lengkap }}"
                            class="w-full h-full object-cover"
                        >

                    @else

                        <div
                            class="
                                w-full h-full
                                flex items-center justify-center
                                text-xl font-bold
                                text-white
                            "
                        >
                            {{
                                strtoupper(
                                    substr($siswa->nama_lengkap, 0, 2)
                                )
                            }}
                        </div>

                    @endif

                </div>

                <div
                    class="
                        absolute
                        -bottom-1
                        -right-1
                        w-5 h-5
                        rounded-full
                        bg-emerald-400
                        border-[3px]
                        border-[#00A39D]
                    "
                ></div>

            </div>

            {{-- PROFILE --}}
            <div class="flex-1">

                <div>
                    <div class="text-xs text-white/70">
                        Nama Siswa
                    </div>

                    <div class="text-lg font-bold leading-tight">
                        {{ $siswa->nama_lengkap }}
                    </div>
                </div>

                <div
                    class="
                        flex
                        flex-wrap
                        gap-2
                        mt-2
                    ">

                    <span
                        class="
                            inline-flex
                            items-center
                            gap-1

                            px-3
                            py-1

                            rounded-full

                            bg-emerald-500/20
                            border
                            border-emerald-300/30

                            text-[11px]
                            font-medium
                        ">

                        <span>●</span>

                        {{ $siswa->status_siswa }}

                    </span>

                    <span
                        class="
                            px-3
                            py-1

                            rounded-full

                            bg-white/15
                            backdrop-blur-sm

                            border
                            border-white/20

                            text-[11px]
                            font-medium
                        ">

                        {{ $semesterAktif?->semester }}

                    </span>

                </div>

            </div>

        </div>

        {{-- DIVIDER --}}
        <div
            class="
                border-t
                border-white/30
                my-4
            ">
        </div>

        {{-- INFO --}}
        <div
            class="
                grid
                grid-cols-3
                gap-3
            ">

            <div
                class="
                    rounded-2xl

                    bg-white/10
                    backdrop-blur-sm

                    border
                    border-white/10

                    p-3
                ">

                <div
                    class="
                        text-[11px]
                        text-white/70
                        mb-1
                    ">

                    NIS

                </div>

                <div
                    class="
                        text-sm
                        font-semibold
                    ">

                    {{ $siswa->nis }}

                </div>

            </div>

            <div
                class="
                    rounded-2xl

                    bg-white/10
                    backdrop-blur-sm

                    border
                    border-white/10

                    p-3
                ">

                <div
                    class="
                        text-[11px]
                        text-white/70
                        mb-1
                    ">

                    NISN

                </div>

                <div
                    class="
                        text-sm
                        font-semibold
                    ">

                    {{ $siswa->nisn }}

                </div>

            </div>

            <div
                class="
                    rounded-2xl

                    bg-white/10
                    backdrop-blur-sm

                    border
                    border-white/10

                    p-3
                ">

                <div
                    class="
                        text-[11px]
                        text-white/70
                        mb-1
                    ">

                    Kelas

                </div>

                <div
                    class="
                        text-sm
                        font-semibold
                    ">

                    {{ $siswa->kelas?->nama ?? '-' }}

                </div>

            </div>

        </div>

    </div>

    </div>


    {{-- HEADER TAGIHAN --}}
    <div class="flex items-center justify-between mt-7 mb-4">

        <div class="flex items-center gap-2">

            <div
                class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center">

                <svg xmlns="http://www.w3.org/2000/svg"
                     fill="none"
                     viewBox="0 0 24 24"
                     stroke-width="2"
                     stroke="currentColor"
                     class="w-4 h-4 text-red-500">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/>
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          d="M10.34 3.94L1.82 18a1.875 1.875 0 001.6 2.81h17.16a1.875 1.875 0 001.6-2.81L13.66 3.94a1.875 1.875 0 00-3.32 0z"/>
                </svg>

            </div>

            <div class="font-bold text-base">
                Tagihan Aktif
            </div>

            <span
                class="bg-red-50 text-red-500 px-2 py-1 rounded-xl text-[11px] font-semibold">
                {{ $tagihanAktif->count() }}
            </span>

        </div>

        <a href="{{ route('wali.keuangan') }}"
           class="text-[#00A39D] text-sm font-semibold">
            Semua →
        </a>

    </div>

    {{-- LIST TAGIHAN --}}
    @foreach($tagihanAktif as $tagihan)

        @php

        $status = strtolower(trim($tagihan->status));

        switch ($status) {

            case 'lunas':

                $cardClass   = 'bg-green-50 border-green-200';
                $iconClass   = 'bg-green-100 text-green-600';
                $amountClass = 'text-green-600';
                $badgeClass  = 'bg-green-100 text-green-700';

                break;

            case 'sebagian':

                $cardClass   = 'bg-yellow-50 border-yellow-300';
                $iconClass   = 'bg-yellow-100 text-yellow-600';
                $amountClass = 'text-yellow-700';
                $badgeClass  = 'bg-yellow-100 text-yellow-700';

                break;

            case 'belum':

                $cardClass   = 'bg-red-50 border-red-200';
                $iconClass   = 'bg-red-100 text-red-500';
                $amountClass = 'text-red-500';
                $badgeClass  = 'bg-red-100 text-red-600';

                break;

            default:

                $cardClass   = 'bg-slate-50 border-slate-200';
                $iconClass   = 'bg-slate-100 text-slate-500';
                $amountClass = 'text-slate-500';
                $badgeClass  = 'bg-slate-100 text-slate-500';

                break;
        }

    @endphp

    <div class="rounded-[20px] border {{ $cardClass }} p-4 mb-4">

        <div class="flex items-center justify-between gap-3">

            {{-- KIRI --}}
            <div class="flex items-center gap-4 flex-1">

            {{-- ICON --}}
            <div
                class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 {{ $iconClass }}">
            
                @if(
                    str_contains($status,'lunas') ||
                    str_contains($status,'sudah bayar')
                )
            
                    {{-- Lunas --}}
                    <x-heroicon-o-check-badge class="w-6 h-6" />
            
                @elseif(str_contains($status,'segera'))
            
                    {{-- Akademik --}}
                    <x-heroicon-o-academic-cap class="w-6 h-6" />
            
                @else
            
                    {{-- Tagihan --}}
                    <x-heroicon-o-wallet class="w-6 h-6" />
            
                @endif
            
            </div>
            
                            {{-- INFO --}}
                            <div>
            
                                <div class="font-semibold text-[16px] leading-tight text-slate-900">
                                    {{ $tagihan->judul }}
                                </div>
            
                                <div class="mt-1 text-[12px] text-slate-600">
            
                                    Jatuh tempo:
                                    {{ optional($tagihan->jatuh_tempo)->format('d M Y') }}
                                
                                </div>
            
                            </div>
            
                        </div>
            
                        {{-- KANAN --}}
                        <div class="text-right">
            
                            <div class="font-bold text-[18px] leading-tight {{ $amountClass }}">
                                Rp {{ number_format($tagihan->nominal,0,',','.') }}
                            </div>
            
                            <span
                                class="inline-flex mt-2 px-3 py-1 rounded-xl text-[11px] font-semibold {{ $badgeClass }}">
                                {{ $tagihan->status }}
                            </span>
            
                        </div>
            
                    </div>
            
                </div>
            
            @endforeach

    {{-- TOTAL TAGIHAN --}}
    <div
        class="bg-[#FF0000] rounded-[15px] p-4 mt-5 text-white flex items-center justify-between shadow-lg">

        <div>

            <div class="text-sm text-white/80">
                Total Tagihan
            </div>

            <div class="text-xl font-bold leading-none mt-1">
                Rp {{ number_format($totalTagihan,0,',','.') }}
            </div>

        </div>

        <a href="{{ route('wali.keuangan') }}"
           class="bg-white text-[#FF0000] px-4 py-3 rounded-2xl font-semibold text-sm shadow-sm">
            Bayar Sekarang →
        </a>

    </div>

{{-- MENU UTAMA SAAS --}}
<div class="mt-7">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-[#00A39D]"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M3.75 4.5h6v6h-6v-6zm0 9h6v6h-6v-6zm9-9h6v6h-6v-6zm0 9h6v6h-6v-6z" />
                </svg>
            </div>
            <div>
                <div class="text-base font-bold text-slate-900">
                    Menu Utama
                </div>
                <div class="text-xs text-slate-500">
                    Akses Fitur Utama Santri
                </div>
            </div>

        </div>
    </div>

    {{-- MAIN CARD --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">

        <div class="grid grid-cols-3 sm:grid-cols-3 gap-3">

            {{-- TAHFIDZ --}}
            <a href="{{ route('wali.tahfidz') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-cyan-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-cyan-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6.253v13M12 6.253C10.832 5.477 9.246 5 7.5 5A4.5 4.5 0 003 9.5v9A4.5 4.5 0 017.5 14c1.746 0 3.332.477 4.5 1.253M12 6.253C13.168 5.477 14.754 5 16.5 5A4.5 4.5 0 0121 9.5v9A4.5 4.5 0 0016.5 14c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>

                <div class="font-semibold text-sm text-slate-900">Tahfidz</div>
                <div class="text-[11px] text-slate-500 mt-1">Hafalan Al-Qur’an</div>

            </a>

            {{-- ABSENSI --}}
            <a href="{{ route('wali.absensi') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-blue-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75L11.25 15 15 9.75"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="font-semibold text-sm text-slate-900">Absensi</div>
                <div class="text-[11px] text-slate-500 mt-1">Kehadiran harian</div>

            </a>

            {{-- PELANGGARAN --}}
            <a href="{{ route('wali.pelanggaran') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-orange-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.34 3.94L1.82 18a1.875 1.875 0 001.6 2.81h17.16a1.875 1.875 0 001.6-2.81L13.66 3.94a1.875 1.875 0 00-3.32 0z"/>
                    </svg>
                </div>

                <div class="font-semibold text-sm text-slate-900">Pelanggaran</div>
                <div class="text-[11px] text-slate-500 mt-1">Catatan disiplin</div>

            </a>

            {{-- PRESTASI --}}
            <a href="{{ route('wali.prestasi') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-yellow-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11.48 3.5a.56.56 0 011.04 0l2.12 5.11a.56.56 0 00.48.35l5.52.44a.56.56 0 01.32.99l-4.2 3.6a.56.56 0 00-.18.56l1.28 5.38a.56.56 0 01-.84.61L12 17.06l-4.72 2.89a.56.56 0 01-.84-.61l1.28-5.38a.56.56 0 00-.18-.56l-4.2-3.6a.56.56 0 01.32-.99l5.52-.44a.56.56 0 00.48-.35l2.12-5.11z"/>
                    </svg>
                </div>

                <div class="font-semibold text-sm text-slate-900">Prestasi</div>
                <div class="text-[11px] text-slate-500 mt-1">Capaian siswa</div>

            </a>

            {{-- PERIZINAN --}}
            <a href="{{ route('wali.perizinan') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-violet-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-3.75 0h16.5A1.125 1.125 0 0121.375 11.625v7.125A1.125 1.125 0 0120.25 20.25H3.75A1.125 1.125 0 012.625 18.75V11.625A1.125 1.125 0 013.75 10.5z"/>
                    </svg>
                </div>

                <div class="font-semibold text-sm text-slate-900">Perizinan</div>
                <div class="text-[11px] text-slate-500 mt-1">Ajukan izin</div>

            </a>

            {{-- RAPORT --}}
            <a href="{{ route('wali.raport') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-emerald-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 13h18M3 6h18M3 20h18"/>
                    </svg>
                </div>

                <div class="font-semibold text-sm text-slate-900">Raport</div>
                <div class="text-[11px] text-slate-500 mt-1">Nilai akademik</div>

            </a>

        </div>
    </div>
</div>

{{-- PENGUMUMAN SAAS TEAL MODERN --}}
@if($pengumuman->count())

<div class="mt-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-[#00A39D]"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.8"
                    stroke="currentColor">

                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.311 6.022c1.733.64 3.56 1.085 5.455 1.31m5.713 0a24.255 24.255 0 0 1-5.713 0m5.713 0a3 3 0 11-5.713 0" />

                </svg>
            </div>
            <div>
                <div class="text-base font-bold text-slate-900">
                    Pengumuman
                </div>
                <div class="text-xs text-slate-500">
                    Informasi terbaru dari sekolah
                </div>
            </div>

        </div>

        <a href="{{ route('wali.pengumuman') }}"
           class="text-sm font-semibold text-[#00A39D] hover:text-[#008f87] transition">
            Lihat semua →
        </a>
    </div>

    {{-- MAIN SAAS CARD --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">

        {{-- HEADER CARD --}}
        <div class="px-5 py-4 bg-gradient-to-r from-[#00A39D] to-emerald-400 text-white">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-2 text-sm font-semibold">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-4 h-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="2"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                    </svg>

                    Announcement Feed
                </div>

                <span class="text-xs bg-white/20 px-2 py-1 rounded-lg">
                    {{ $pengumuman->count() }} updates
                </span>

            </div>

        </div>

        {{-- LIST --}}
        <div class="divide-y divide-slate-100">

            @foreach($pengumuman as $item)

            <div class="p-4 hover:bg-teal-50/40 transition">

                <div class="flex gap-3">

                    {{-- ICON --}}
                    <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center flex-shrink-0">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 text-[#00A39D]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.8"
                            stroke="currentColor">

                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.311 6.022c1.733.64 3.56 1.085 5.455 1.31m5.713 0a24.255 24.255 0 0 1-5.713 0m5.713 0a3 3 0 11-5.713 0" />

                        </svg>

                    </div>

                    {{-- CONTENT --}}
                    <div class="flex-1">

                        <div class="flex items-start justify-between gap-2">

                            <div class="font-semibold text-slate-900 text-sm">
                                {{ $item->title }}
                            </div>

                            @if($item->is_pinned ?? false)
                                <span class="text-[10px] px-2 py-1 rounded-full bg-red-50 text-red-600 font-semibold">
                                    pinned
                                </span>
                            @endif

                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-3.5 h-3.5"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke-width="2"
                                 stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5h18v11.25A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75z" />
                            </svg>

                            {{ $item->created_at->format('d M Y • H:i') }}

                        </div>

                        <div class="text-sm text-slate-600 mt-2 leading-relaxed line-clamp-2">
                            {!! Str::limit(strip_tags($item->content), 120) !!}
                        </div>

                        @if($item->attachment)
                            <a href="{{ asset('storage/' . $item->attachment) }}"
                               class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-[#00A39D] hover:text-[#008f87] transition">

                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-3.5 h-3.5"
                                     fill="none"
                                     viewBox="0 0 24 24"
                                     stroke-width="2"
                                     stroke="currentColor">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M19.5 21H6.75A2.25 2.25 0 014.5 18.75V5.25A2.25 2.25 0 016.75 3h7.086a2.25 2.25 0 011.591.659l4.414 4.414A2.25 2.25 0 0120.25 9.75V18.75A2.25 2.25 0 0119.5 21z" />
                                </svg>
                                Lihat lampiran
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endif
</div>

@endsection