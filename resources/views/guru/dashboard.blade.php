@extends('guru.layout.guru')

@section('content')

<div class="px-4 pb-4 bg-[#F8FAFB] min-h-screen">
   {{-- HEADER --}}
    <div class="-mx-4 mb-3 px-4 pt-2 pb-1">
        
        {{-- NOTIF --}}
        
        @if(session('warning'))
    
        <div
            id="alertWarning"
            class="mt-4 mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
        
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100">
        
                <x-heroicon-o-exclamation-triangle
                    class="h-5 w-5 text-amber-600"/>
        
            </div>
        
            <div class="flex-1">
        
            <div class="text-[13px] font-semibold leading-none text-amber-800">
                Pemberitahuan
            </div>
        
            <div class="mt-px text-[13px] leading-5 text-amber-700">
                {{ session('warning') }}
            </div>
        
            </div>
        
        </div>
        
        <script>
        
        setTimeout(() => {
        
            const alert = document.getElementById('alertWarning');
        
            if (alert) {
        
                alert.classList.add('opacity-0', '-translate-y-2');
        
                setTimeout(() => alert.remove(), 500);
        
            }
        
        }, 5000);
        
        </script>
        
        @endif

        @if(session('success'))

        <div
            id="alertSuccess"
            class="mt-4 mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">

            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100">

                <x-heroicon-o-check-circle
                    class="h-5 w-5 text-emerald-600"/>

            </div>

            <div class="flex-1">

            <div class="text-[13px] font-semibold leading-none text-emerald-800">
                Berhasil
            </div>

            <div class="mt-px text-[13px] leading-5 text-emerald-700">
                {{ session('success') }}
            </div>

            </div>

        </div>

        <script>

        setTimeout(() => {

            const alert = document.getElementById('alertSuccess');

            if (alert) {

                alert.classList.add('opacity-0', '-translate-y-2');

                setTimeout(() => alert.remove(), 500);

            }

        }, 5000);

        </script>

        @endif

        <div class="flex items-center justify-between px-3 py-2">
    
            {{-- INFORMASI GURU --}}
            <div>
    
                <div class="text-xs text-slate-500">
                    Assalamualaikum, Selamat Datang
                </div>
    
                <div class="font-bold text-base text-slate-900">
                    {{ session('guru_nama') }}
                </div>
    
            </div>
    
            {{-- TANGGAL --}}
            <div class="text-right">
    
                <div class="flex items-center justify-end gap-1 text-xs text-slate-500">
    
                    <x-heroicon-o-calendar-days class="w-4 h-4"/>
    
                    {{ now()->translatedFormat('l') }}
    
                </div>
    
                <div class="mt-1 text-sm font-semibold text-slate-900">
                    {{ now()->translatedFormat('d M Y') }}
                </div>
    
            </div>
    
        </div>
    
    </div>
    

    {{-- CARD GURU --}}
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
    
                {{-- FOTO GURU --}}
                <div class="relative">
    
                    <div
                        class="
                            w-[72px]
                            h-[72px]
                            rounded-2xl
                            overflow-hidden
                            bg-white/20
                            backdrop-blur-sm
                            border border-white/20
                            shadow-lg
                        ">
    
                        @if($guru->foto)
    
                            <img
                                src="{{ asset('storage/' . $guru->foto) }}"
                                alt="{{ $guru->nama }}"
                                class="w-full h-full object-cover">
                        
                        @else
                        
                            <div class="w-full h-full flex items-center justify-center">
                                <x-heroicon-o-user class="w-10 h-10 text-white"/>
                            </div>
                        
                        @endif
    
                    </div>
    
                    {{-- STATUS --}}
                    <div
                        class="
                            absolute
                            -bottom-1
                            -right-1
                            w-5
                            h-5
                            rounded-full
                            bg-emerald-400
                            border-[3px]
                            border-[#00A39D]
                        ">
                    </div>
    
                </div>
    
                {{-- PROFILE --}}
                <div class="flex-1">
    
                    <div class="text-xs text-white/70">
                        Nama Guru
                    </div>
    
                    <div class="text-lg font-bold leading-tight">
                        {{ $guru->nama }}
                    </div>
    
                    <div class="flex flex-wrap gap-2 mt-2">
    
                        {{-- STATUS --}}
                        <span
                            class="
                                inline-flex
                                items-center
                                gap-1
                                px-3
                                py-1
                                rounded-full
                                bg-emerald-500/20
                                border border-emerald-300/30
                                text-[11px]
                                font-medium
                            ">
    
                            <x-heroicon-s-check-circle class="w-3.5 h-3.5"/>
    
                            Guru Aktif
    
                        </span>
    
                        {{-- SEMESTER --}}
                        <span
                            class="
                                inline-flex
                                items-center
                                gap-1
                                px-3
                                py-1
                                rounded-full
                                bg-white/15
                                backdrop-blur-sm
                                border border-white/20
                                text-[11px]
                                font-medium
                            ">
    
                            <x-heroicon-o-calendar-days class="w-3.5 h-3.5"/>
    
                            {{ $semesterAktif?->semester ?? '-' }}
    
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

{{-- INFO GURU --}}
<div
    class="
        grid
        grid-cols-3
        gap-3
    ">

    {{-- NIY --}}
    <div
        class="
            rounded-2xl
            bg-white/10
            backdrop-blur-sm
            border border-white/10
            p-3
        ">

        <div
            class="
                flex items-center gap-1
                text-[11px]
                text-white/70
                mb-1
            ">

            <x-heroicon-o-identification class="w-3 h-3"/>

            NIY

        </div>

        <div
            class="
                text-sm
                font-semibold
            ">

            {{ $guru->niy }}

        </div>

    </div>

    {{-- JABATAN --}}
    <div
        class="
            rounded-2xl
            bg-white/10
            backdrop-blur-sm
            border border-white/10
            p-3
        ">

        <div
            class="
                flex items-center gap-1
                text-[11px]
                text-white/70
                mb-1
            ">

            <x-heroicon-o-briefcase class="w-3 h-3"/>

            Jabatan

        </div>

        <div
            class="
                text-sm
                font-semibold
                truncate
            ">

            {{ $guru->jabatan ?? 'Guru' }}

        </div>

    </div>

    {{-- LEMBAGA --}}
    <div
        class="
            rounded-2xl
            bg-white/10
            backdrop-blur-sm
            border border-white/10
            p-3
        ">

        <div
            class="
                flex items-center gap-1
                text-[11px]
                text-white/70
                mb-1
            ">

            <x-heroicon-o-building-office-2 class="w-3 h-3"/>

            Lembaga

        </div>

        <div
            class="
                text-sm
                font-semibold
                truncate
            ">

            {{ $guru->lembagas->first()?->nama ?? '-' }}

        </div>

    </div>

</div>

</div>

</div>

    {{-- HEADER JADWAL --}}
<div class="flex items-center justify-between mt-7 mb-4">

    <div class="flex items-center gap-2">

        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
            <x-heroicon-o-calendar-days class="w-5 h-5 text-blue-600"/>
        </div>

        <div class="font-bold text-base text-slate-900">
            Jadwal Mengajar Hari Ini
        </div>

        <span class="bg-blue-50 text-blue-600 px-2.5 py-1 rounded-xl text-[11px] font-semibold">
            {{ $jadwalHariIni->count() }}
        </span>

    </div>

    <a href="{{ route('guru.jadwal') }}"
       class="text-[#00A39D] text-sm font-semibold hover:text-[#00897B] transition">
        Lihat Semua →
    </a>

</div>

{{-- LIST JADWAL --}}
@forelse($jadwalHariIni as $jadwal)

<div class="rounded-[22px] border border-slate-200 bg-white p-4 mb-4 shadow-sm">

    <div class="flex items-center justify-between gap-4">

        {{-- KIRI --}}
        <div class="flex items-center gap-4 flex-1">

            <div class="w-12 h-12 rounded-2xl bg-teal-50 flex items-center justify-center flex-shrink-0">
                <x-heroicon-o-academic-cap class="w-6 h-6 text-[#00A39D]"/>
            </div>

            <div>

                <div class="font-semibold text-[16px] leading-tight text-slate-900">
                    {{ $jadwal->mataPelajaran->nama ?? '-' }}
                </div>
            
                <div class="mt-0.5 text-[11px] font-medium text-slate-500">
            
                    {{ $jadwal->kelas->nama ?? '-' }}
            
                    <span class="mx-2 text-slate-300">•</span>
            
                    {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
            
                </div>
            
            </div>

        </div>

        {{-- STATUS --}}
        <div class="text-right">

            @if($jadwal->jurnal_sudah_diisi)
        
                <span
                    class="inline-flex items-center gap-1.5
                           rounded-full
                           bg-emerald-50
                           px-2.5 py-1
                           text-[10px]
                           font-medium
                           text-emerald-700">
        
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
        
                    Selesai
        
                </span>
        
            @else
        
                <span
                    class="inline-flex items-center gap-1.5
                           rounded-full
                           bg-red-50
                           px-2.5 py-1
                           text-[10px]
                           font-medium
                           text-red-700">
        
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
        
                    Belum Jurnal
        
                </span>
        
            @endif
        
        </div>

    </div>

</div>

@empty

<div class="bg-white rounded-[22px] border border-slate-200 p-8 text-center">

    <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center">

        <x-heroicon-o-calendar-days class="w-8 h-8 text-slate-400"/>

    </div>

    <div class="mt-4 font-semibold text-slate-700">
        Tidak ada jadwal mengajar hari ini
    </div>

    <div class="text-sm text-slate-500 mt-1">
        Selamat menikmati waktu istirahat.
    </div>

</div>

@endforelse

{{-- RINGKASAN --}}
<div class="bg-gradient-to-r from-[#00A39D] to-[#14C8C0] rounded-[20px] p-5 mt-5 text-white shadow-lg">

    <div class="flex items-center justify-between">

        <div>

            <div class="text-sm text-white/80">
                Total Jadwal Hari Ini
            </div>

            <div class="text-3xl font-bold mt-1">
                {{ $jadwalHariIni->count() }}
            </div>

            <div class="text-xs text-white/80 mt-1">
                Sesi Pembelajaran
            </div>

        </div>

        <a href="{{ route('guru.jurnal') }}"
           class="bg-white text-[#00A39D] px-5 py-3 rounded-2xl font-semibold text-sm shadow">
            Isi Jurnal →
        </a>

    </div>

</div>

{{-- MENU UTAMA --}}
<div class="mt-7">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-4">

        <div class="flex items-center gap-3">

            <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center">
                <x-heroicon-o-squares-2x2 class="w-5 h-5 text-[#00A39D]" />
            </div>

            <div>
                <div class="text-base font-bold text-slate-900">
                    Menu Utama
                </div>

                <div class="text-xs text-slate-500">
                    Akses fitur utama Guru
                </div>
            </div>

        </div>

    </div>

    {{-- CARD --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4">

        <div class="grid grid-cols-3 gap-3">

            {{-- JADWAL --}}
            <a href="{{ route('guru.jadwal') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-blue-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mb-2">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-blue-600"/>
                </div>

                <div class="font-semibold text-sm text-slate-900">
                    Jadwal
                </div>

                <div class="text-[11px] text-slate-500 mt-1">
                    Jadwal mengajar
                </div>

            </a>

            {{-- JURNAL --}}
            <a href="{{ route('guru.jurnal') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-teal-50 to-white hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center mb-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-teal-600"/>
                </div>
                <div class="font-semibold text-sm text-slate-900">
                    Jurnal
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Jurnal mengajar
                </div>
            </a>
            {{-- JURNAL PENGGANTI --}}
            @if (\App\Models\Yayasan::find(session('active_public_yayasan_id'))?->hasFeature(\App\Support\FeatureGate::GURU_PENGGANTI))
            <a href="{{ route('guru.jurnal.pengganti') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-amber-50 to-white hover:shadow-md transition">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center mb-2">
                    <x-heroicon-o-user-group class="w-5 h-5 text-amber-600"/>
                </div>
                <div class="font-semibold text-sm text-slate-900">
                    Jurnal Pengganti
                </div>
                <div class="text-[11px] text-slate-500 mt-1">
                    Gantikan guru lain
                </div>
            </a>
            @endif
            {{-- ABSENSI --}}
            <a href="{{ route('guru.absensi') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-emerald-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center mb-2">
                    <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600"/>
                </div>

                <div class="font-semibold text-sm text-slate-900">
                    Absensi
                </div>

                <div class="text-[11px] text-slate-500 mt-1">
                    Kehadiran siswa
                </div>

            </a>

            {{-- NILAI --}}
            <a href="{{ route('guru.nilai') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-yellow-50 to-white hover:shadow-md transition">

                <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center mb-2">
                    <x-heroicon-o-academic-cap class="w-5 h-5 text-yellow-600"/>
                </div>

                <div class="font-semibold text-sm text-slate-900">
                    Nilai
                </div>

                <div class="text-[11px] text-slate-500 mt-1">
                    Input penilaian
                </div>

            </a>
            
            {{-- GAJI --}}
            <a href="{{ route('guru.gaji') }}"
               class="group p-3 rounded-2xl border border-slate-100 bg-gradient-to-br from-emerald-50 to-white hover:shadow-md transition">
            
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center mb-2">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-600"/>
                </div>
            
                <div class="font-semibold text-sm text-slate-900">
                    Gaji
                </div>
            
                <div class="text-[11px] text-slate-500 mt-1">
                    Slip & riwayat gaji
                </div>
            
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
                <x-heroicon-o-megaphone class="w-6 h-6 text-[#00A39D]" />
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

        <a href="{{ route('guru.pengumuman') }}"
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