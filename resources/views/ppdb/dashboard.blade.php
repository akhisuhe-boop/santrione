@extends('ppdb.layout.ppdb')
@section('content')
@php

$totalStep = count($progress['steps']);
$currentStep = $progress['current'] + 1;
$percent = intval(($currentStep / $totalStep) * 100);
switch ($ppdb->status) {
    case 'draft':
        $statusTitle = 'Akun Berhasil Dibuat';
        $statusColor = 'bg-blue-500';
        $button = true;
        $buttonText = 'Bayar Formulir Pendaftaran';
        $buttonRoute = route('ppdb.pembayaran');
        break;

    case 'menunggu_pembayaran':
        $statusTitle = 'Menunggu Pembayaran';
        $statusColor = 'bg-amber-500';
        $button = true;
        $buttonText = 'Bayar Sekarang';
        $buttonRoute = route('ppdb.pembayaran');
        break;

    case 'formulir':
        $statusTitle = 'Lengkapi Formulir';
        $statusColor = 'bg-emerald-500';
        $button = true;
        $buttonText = 'Isi Formulir';
        $buttonRoute = route('ppdb.formulir');
        break;

    case 'upload_berkas':
        $statusTitle = 'Upload Berkas';
        $statusColor = 'bg-indigo-500';
        $button = true;
        $buttonText = 'Upload Berkas';
        $buttonRoute = route('ppdb.upload-berkas');
        break;

    case 'verifikasi_berkas':
        $statusTitle = 'Verifikasi Berkas';
        $statusColor = 'bg-orange-500';
        $button = false;
        break;

    case 'tes':
        $statusTitle = 'Tes Seleksi';
        $statusColor = 'bg-purple-500';
        $button = false;
        break;

    case 'lulus':
        $statusTitle = 'Lulus Seleksi';
        $statusColor = 'bg-emerald-500';
        $button = false;
        break;

    case 'daftar_ulang':
        $statusTitle = 'Daftar Ulang';
        $statusColor = 'bg-cyan-500';
        $button = true;
        $buttonText = 'Lanjutkan';
        $buttonRoute = route('ppdb.pembayaran');
        break;

    default:
        $statusTitle = 'Peserta Didik Aktif';
        $statusColor = 'bg-emerald-500';
        $button = false;
        break;
}

@endphp

<div class="min-h-screen bg-[#F8FAFB] pb-24">
    
    {{-- HERO --}}
    <section class="px-5 pt-5">
    @if(session('success'))
        <div
            x-data="{ show:true }"
            x-show="show"
            x-transition
            class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
    
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-5 h-5 text-emerald-600"
                             fill="none"
                             viewBox="0 0 24 24"
                             stroke="currentColor">
    
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M5 13l4 4L19 7"/>
    
                        </svg>
                    </div>
                    <div>
    
                        <h4 class="font-semibold text-emerald-700">
                            Berhasil
                        </h4>
    
                        <p class="mt-1 text-sm text-emerald-600">
                            {{ session('success') }}
                        </p>
    
                    </div>
                </div>
    
                <button
                    @click="show=false"
                    class="text-emerald-500 hover:text-emerald-700">
    
                    ✕
    
                </button>
            </div>
        </div>
        @endif
    
        <div class="relative overflow-hidden rounded-[28px]
                    bg-gradient-to-br from-[#00A39D] via-[#00B4AC] to-[#14C8C0]
                    p-5 text-white shadow-xl">
    
            {{-- Ornament --}}
            <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/10 -mr-16 -mt-16"></div>
    
            <div class="absolute bottom-0 left-0 w-28 h-28 rounded-full bg-white/5 -ml-10 -mb-10"></div>
    
            <div class="relative z-10">
    
                {{-- HEADER --}}
                <div class="flex items-center gap-4">
    
                    <div class="relative shrink-0">

                        <div class="w-[68px] h-[68px] rounded-2xl overflow-hidden
                                    bg-white/15 backdrop-blur-sm border border-white/20">
                    
                            @if($ppdb->foto)
                    
                                <img
                                    src="{{ asset('storage/' . $ppdb->foto) }}"
                                    alt="{{ $ppdb->nama_lengkap }}"
                                    class="w-full h-full object-cover">
                    
                            @else
                    
                                <div class="w-full h-full flex items-center justify-center">
                    
                                    <x-heroicon-o-user class="w-8 h-8 text-white/80" />
                    
                                </div>
                    
                            @endif
                    
                        </div>
                    
                        <div
                            class="absolute
                                   -bottom-1
                                   -right-1
                                   w-5
                                   h-5
                                   rounded-full
                                   bg-emerald-400
                                   border-[3px]
                                   border-[#00A39D]">
                        </div>
                    
                    </div>
    
                    <div class="flex-1">
    
                        <div class="text-xs text-white/70">
                            Nama Calon Siswa
                        </div>
    
                        <h2 class="mt-1 text-xl font-bold leading-tight">
                            {{ $ppdb->nama_lengkap }}
                        </h2>
    
                        <div class="mt-1 text-sm text-white/80">
                            Status :
                            <span class="font-semibold text-white">
                                {{ $statusTitle }}
                            </span>
                        </div>
    
                    </div>
    
                </div>
    
                {{-- Divider --}}
                <div class="my-5 border-t border-white/20"></div>
    
                {{-- Progress --}}
                <div>
                
                    <div class="flex items-center justify-between mb-3">
                
                        <span class="text-sm font-semibold">
                            Progress PPDB
                        </span>
                
                        <span class="text-xs font-medium text-white/80">
                            Tahap {{ $progress['current'] + 1 }}
                            dari {{ count($progress['steps']) }}
                        </span>
                
                    </div>
                
                    <div class="flex items-center gap-2">
                
                        @foreach($progress['steps'] as $index => $step)
                
                            <div
                                class="flex-1 h-2 rounded-full transition-all duration-300
                
                                @if($index < $progress['current'])
                                    bg-white
                                @elseif($index == $progress['current'])
                                    bg-white/90
                                @else
                                    bg-white/20
                                @endif">
                            </div>
                
                        @endforeach
                
                    </div>
                
                    <div class="mt-2 text-xs text-white/80">
                
                        {{ $progress['steps'][$progress['current']] }}
                
                    </div>
                
                </div>
    
                @if($button)
    
                    <a
                        href="{{ $buttonRoute }}"
                        class="mt-6 h-12 rounded-2xl bg-white
                               flex items-center justify-center gap-2
                               font-semibold text-[#00A39D]
                               transition hover:bg-slate-100">
    
                        <x-heroicon-o-arrow-right-circle class="w-5 h-5"/>
    
                        {{ $buttonText }}
    
                    </a>
    
                @endif
    
            </div>
    
        </div>
    
    </section>
    
    {{-- QUICK MENU --}}
    <section class="px-5 pt-6">

    <div class="bg-white rounded-[28px] border border-slate-200 shadow-sm p-5">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-5">

            <div>
                <h2 class="text-base font-bold text-slate-900">
                    Menu Utama
                </h2>

                <p class="mt-0.5 text-sm text-slate-500">
                    Akses seluruh proses PPDB
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-[#00A39D]/10 flex items-center justify-center">
                <x-heroicon-o-squares-2x2 class="w-6 h-6 text-[#00A39D]" />
            </div>
        </div>

        {{-- MENU --}}
        <div class="grid grid-cols-3 gap-3">
        
            {{-- Formulir --}}
            <a href="{{ route('ppdb.formulir') }}"
               class="group rounded-2xl border border-slate-200/70
                      bg-gradient-to-br from-sky-50 to-white
                      p-4 transition-all duration-300
                      hover:-translate-y-1
                      hover:shadow-md
                      hover:border-sky-200">
        
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100">
                    <x-heroicon-o-document-text class="h-5 w-5 text-sky-600"/>
                </div>
        
                <div class="mt-5">
                    <h3 class="text-[15px] font-semibold text-slate-900">
                        Formulir
                    </h3>
        
                    <p class="mt-1 text-xs text-slate-500">
                        Biodata peserta
                    </p>
                </div>
        
            </a>
        
            {{-- Berkas --}}
            <a href="{{ route('ppdb.formulir') }}"
               class="group rounded-2xl border border-slate-200/70
                      bg-gradient-to-br from-violet-50 to-white
                      p-4 transition-all duration-300
                      hover:-translate-y-1
                      hover:shadow-md
                      hover:border-violet-200">
        
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-100">
                    <x-heroicon-o-folder-open class="h-5 w-5 text-violet-600"/>
                </div>
        
                <div class="mt-5">
                    <h3 class="text-[15px] font-semibold text-slate-900">
                        Berkas
                    </h3>
        
                    <p class="mt-1 text-xs text-slate-500">
                        Upload dokumen
                    </p>
                </div>
        
            </a>
        
            {{-- Profil --}}
            <a href="{{ route('ppdb.profil') }}"
               class="group rounded-2xl border border-slate-200/70
                      bg-gradient-to-br from-amber-50 to-white
                      p-4 transition-all duration-300
                      hover:-translate-y-1
                      hover:shadow-md
                      hover:border-amber-200">
        
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100">
                    <x-heroicon-o-user-circle class="h-5 w-5 text-amber-600"/>
                </div>
        
                <div class="mt-5">
                    <h3 class="text-[15px] font-semibold text-slate-900">
                        Profil
                    </h3>
        
                    <p class="mt-1 text-xs text-slate-500">
                        Akun peserta
                    </p>
                </div>
        
            </a>
        
        </div>
        
            </div>
        
        </section>
    
        {{-- TAGIHAN PPDB --}}

        <section class="px-5 mt-6">
            <div class="flex items-center gap-4 mb-4">

        {{-- Icon --}}
                <div
                    class="w-11 h-11 rounded-2xl
                           bg-[#00A39D]/10
                           flex items-center justify-center
                           shrink-0">
            
                    <x-heroicon-o-credit-card
                        class="w-6 h-6 text-[#00A39D]" />
            
                </div>
            
                {{-- Text --}}
                <div class="space-y-0.5">

                    <h2 class="text-base font-bold text-slate-900 leading-5">
                        Pembayaran
                    </h2>
                
                    <p class="text-sm text-slate-500 leading-5">
                        Status pembayaran formulir PPDB
                    </p>
                </div>
            </div>
            
            @if($tagihan)
            @php

            // Status TAGIHAN (belum/sebagian/lunas) itu akumulasi dari
            // SEMUA pembayaran sukses -- ini yang benar dipakai untuk
            // menentukan LUNAS atau belum, bukan status transaksi
            // pembayaran TERAKHIR saja (yang sebelumnya salah dipakai,
            // jadi cicilan pertama langsung dikira LUNAS).
            $tagihanStatus = $tagihan->status;
            $latestStatus  = $pembayaran?->status;

            if ($latestStatus === 'pending') {
                $badgeText  = 'MENUNGGU VERIFIKASI';
                $badgeClass = 'bg-blue-100 text-blue-700';
            } elseif ($latestStatus === 'gagal' && $tagihanStatus !== 'lunas') {
                $badgeText  = 'DITOLAK';
                $badgeClass = 'bg-red-100 text-red-700';
            } else {
                $badgeText = match($tagihanStatus) {
                    'lunas' => 'LUNAS',
                    'sebagian' => 'DIBAYAR SEBAGIAN',
                    default => 'BELUM BAYAR',
                };
                $badgeClass = match($tagihanStatus) {
                    'lunas' => 'bg-emerald-100 text-emerald-700',
                    'sebagian' => 'bg-amber-100 text-amber-700',
                    default => 'bg-amber-100 text-amber-700',
                };
            }

            $paid = $tagihanStatus === 'lunas';
            @endphp

            <div
            class="relative
                   overflow-hidden
                   rounded-2xl
                   bg-gradient-to-br
                   from-white
                   to-slate-50
                   border
                   border-slate-200
                   shadow-sm">
        
            {{-- Ornament --}}
            <div
                class="absolute
                       -top-8
                       -right-8
                       w-24
                       h-24
                       rounded-full
                       bg-[#00A39D]/5">
            </div>
        
            <div
                class="absolute
                       -bottom-8
                       -left-8
                       w-20
                       h-20
                       rounded-full
                       bg-[#00A39D]/5">
            </div>
        
            <div class="relative p-4">
        
                {{-- Header --}}
                <div class="flex items-start justify-between">
        
                    <div>
        
                        <div class="text-[10px] tracking-wide text-slate-500">
                            Jenis Pembayaran
                        </div>
        
                        <h3
                            class="mt-1
                                   text-base
                                   font-semibold
                                   leading-tight
                                   text-slate-900">
        
                            {{ $tagihan->jenisTagihan->nama ?? 'Formulir PPDB' }}
        
                        </h3>
        
                    </div>
        
                    <span
                        class="px-3
                               py-1
                               rounded-full
                               text-[10px]
                               font-semibold
                               {{ $badgeClass }}">
                    
                        {{ $badgeText }}
                    
                    </span>
        
                </div>
        
                {{-- Nominal --}}
                <div
                    class="mt-4
                           flex
                           items-end
                           justify-between">
        
                    <div>
        
                        <div
                            class="text-xs
                                   text-slate-500">
        
                            Nominal
        
                        </div>
        
                        <div
                            class="mt-1
                                   text-2xl
                                   font-bold
                                   tracking-tight
                                   text-[#00A39D]">
        
                            Rp {{ number_format($tagihan->nominal,0,',','.') }}
        
                        </div>
        
                    </div>
        
                    <div
                        class="w-12
                               h-12
                               rounded-2xl
                               bg-[#00A39D]/10
                               flex
                               items-center
                               justify-center">
        
                        <x-heroicon-o-banknotes
                            class="w-6 h-6 text-[#00A39D]" />
        
                    </div>
        
                </div>
        
                {{-- Jatuh Tempo --}}
                @if(!empty($tagihan->jatuh_tempo))
        
                    <div
                        class="mt-2
                               flex
                               items-center
                               gap-1.5
                               text-xs
                               text-slate-500">
        
                        <x-heroicon-o-calendar
                            class="w-4 h-4" />
        
                        <span>Jatuh Tempo</span>
        
                        <span
                            class="font-semibold
                                   text-slate-700">
        
                            {{ \Carbon\Carbon::parse($tagihan->jatuh_tempo)->translatedFormat('d F Y') }}
        
                        </span>
        
                    </div>
        
                @endif
        
                    {{-- Tombol --}}
                    {{-- Status Pembayaran --}}
                    @if($latestStatus == 'pending')
                    
                        <div
                            class="mt-3
                                   rounded-xl
                                   border
                                   border-blue-200
                                   bg-blue-50
                                   p-3">
                    
                            <div class="flex items-center gap-3">
                    
                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-blue-100
                                           flex items-center justify-center">
                    
                                    <x-heroicon-o-clock
                                        class="w-5 h-5 text-blue-600"/>
                    
                                </div>
                    
                                <div>
                    
                                    <div class="text-sm font-semibold text-blue-700">
                                        Menunggu Verifikasi
                                    </div>
                    
                                    <div class="text-xs text-blue-600">
                                        Bukti transfer sedang diperiksa panitia.
                                    </div>
                    
                                </div>
                    
                            </div>
                    
                        </div>
                    
                    @elseif($latestStatus == 'sukses')
                    
                        <div
                            class="mt-3
                                   rounded-xl
                                   border
                                   border-[#00A39D]/20
                                   bg-[#00A39D]/5
                                   p-3">
                    
                            <div class="flex items-center gap-3">
                    
                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-[#00A39D]/10
                                           flex items-center justify-center">
                    
                                    <x-heroicon-o-check-badge
                                        class="w-5 h-5 text-[#00A39D]"/>
                    
                                </div>
                    
                                <div>
                    
                                    <div
                                        class="text-sm
                                               font-semibold
                                               text-[#00A39D]">
                    
                                        Pembayaran Berhasil
                    
                                    </div>
                    
                                    <div
                                        class="text-xs
                                               text-slate-500">
                    
                                        Silakan lanjut ke tahap berikutnya.
                    
                                    </div>
                    
                                </div>
                    
                            </div>
                    
                        </div>
                    
                    @elseif($latestStatus == 'gagal')
                    
                        <div
                            class="mt-3
                                   rounded-xl
                                   border
                                   border-red-200
                                   bg-red-50
                                   p-3">
                    
                            <div class="flex items-center gap-3">
                    
                                <div
                                    class="w-10 h-10
                                           rounded-xl
                                           bg-red-100
                                           flex items-center justify-center">
                    
                                    <x-heroicon-o-x-circle
                                        class="w-5 h-5 text-red-600"/>
                    
                                </div>
                    
                                <div>
                    
                                    <div
                                        class="text-sm
                                               font-semibold
                                               text-red-700">
                    
                                        Pembayaran Ditolak
                    
                                    </div>
                    
                                    <div
                                        class="text-xs
                                               text-red-600">
                    
                                        Silakan upload ulang bukti transfer.
                    
                                    </div>
                    
                                </div>
                    
                            </div>
                    
                        </div>
                    
                    @else
                    
                        <a
                            href="{{ route('ppdb.pembayaran') }}"
                            class="mt-3
                                   w-full
                                   inline-flex
                                   items-center
                                   justify-center
                                   gap-2
                                   rounded-xl
                                   bg-[#00A39D]
                                   py-2.5
                                   text-sm
                                   font-semibold
                                   text-white
                                   shadow-sm
                                   transition-all
                                   duration-200
                                   hover:bg-[#008B86]
                                   hover:shadow-md">
                    
                            <x-heroicon-o-credit-card
                                class="w-4 h-4"/>
                    
                            Bayar Sekarang
                    
                        </a>
                    
                    @endif
                    
                                </div> {{-- /.relative.p-4 --}}
                            </div> {{-- /.card pembayaran --}}
                    
                    @else
                    
                        <div
                            class="bg-white
                                   rounded-[30px]
                                   border
                                   border-dashed
                                   border-slate-300
                                   p-10
                                   text-center">
                    
                            <div
                                class="mx-auto
                                       w-16
                                       h-16
                                       rounded-3xl
                                       bg-slate-100
                                       flex
                                       items-center
                                       justify-center">
                    
                                <x-heroicon-o-credit-card
                                    class="w-8 h-8 text-slate-400"/>
                    
                            </div>
                    
                            <h3
                                class="mt-5
                                       text-base
                                       font-bold
                                       text-slate-800">
                    
                                Belum Ada Tagihan
                    
                            </h3>
                    
                            <p
                                class="mt-2
                                       text-sm
                                       text-slate-500">
                    
                                Tagihan akan muncul setelah panitia
                                membuat pembayaran PPDB.
                    
                            </p>
                    
                        </div>
                    
                    @endif
                    
                    </section>
    
        {{-- PROGRESS PPDB --}}

        <section class="px-5 mt-6">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-4">
    
            {{-- Icon --}}
            <div
                class="w-11 h-11 rounded-2xl
                       bg-[#00A39D]/10
                       flex items-center justify-center
                       shrink-0">
    
                <x-heroicon-o-clipboard-document-check
                    class="w-6 h-6 text-[#00A39D]" />
    
            </div>
    
            {{-- Text --}}
            <div class="space-y-0.5">
    
                <h2 class="text-base font-bold text-slate-900 leading-5">
                    Progress Pendaftaran
                </h2>
    
                <p class="text-sm text-slate-500 leading-5">
                    Pantau setiap tahapan PPDB
                </p>
    
            </div>
    
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Progress Bar --}}
        <div class="p-5 border-b border-slate-100">
    
            <div class="flex items-center justify-between mb-3">
    
                <span class="text-sm font-semibold text-slate-900">
                    Progress
                </span>
    
                <span class="text-xs text-slate-500">
                    {{ $progress['current'] + 1 }} / {{ count($progress['steps']) }}
                </span>
    
            </div>
    
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
    
                <div
                    class="h-full bg-gradient-to-r from-[#00A39D] to-emerald-400 rounded-full"
                    style="width: {{ (($progress['current'] + 1) / count($progress['steps'])) * 100 }}%">
                </div>
    
            </div>
    
        </div>
    
        {{-- List Step --}}
        <div class="p-4 space-y-3">
    
            @foreach($progress['steps'] as $index => $step)
    
                @php
                    $completed = $index < $progress['current'];
                    $active = $index == $progress['current'];
                @endphp
    
                <div
                class="flex items-center justify-between rounded-2xl p-4 transition-all duration-300
            
                @if($completed)
                    border border-emerald-100
                    bg-emerald-50
                @elseif($active)
                    border border-[#00A39D]/15
                    bg-[#00A39D]/5
                @else
                    border border-slate-200
                    bg-white
                @endif
            
                hover:shadow-sm">
    
                    <div class="flex items-center gap-3">
    
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center
    
                            @if($completed)
                                bg-emerald-100
                            @elseif($active)
                                bg-[#00A39D]/10
                            @else
                                bg-slate-100
                            @endif">
    
                            @if($completed)
    
                                <x-heroicon-s-check class="w-5 h-5 text-emerald-600"/>
    
                            @elseif($active)
    
                                <x-heroicon-s-clock
                                    class="w-5 h-5 text-[#00A39D]"/>
    
                            @else
    
                                <x-heroicon-o-clock
                                    class="w-5 h-5 text-slate-400"/>
    
                            @endif
    
                        </div>
    
                        <div>
    
                            <div
                            class="text-sm font-semibold
                        
                            @if($completed)
                                text-emerald-700
                            @elseif($active)
                                text-[#00A39D]
                            @else
                                text-slate-900
                            @endif">
    
                                {{ $step }}
    
                            </div>
                            
                            <p
                            class="mt-0.5 text-xs
                        
                            @if($completed)
                                text-emerald-600
                            @elseif($active)
                                text-[#00A39D]
                            @else
                                text-slate-500
                            @endif">
                            @switch($index)
                                @case(0)
                                    Akun PPDB berhasil dibuat.
                                @break
                        
                                @case(1)
                                    Lakukan pembayaran formulir.
                                @break
                        
                                @case(2)
                                    Lengkapi data diri peserta.
                                @break
                        
                                @case(3)
                                    Upload seluruh dokumen.
                                @break
                        
                                @case(4)
                                    Menunggu verifikasi panitia.
                                @break
                        
                                @case(5)
                                    Ikuti tes sesuai jadwal.
                                @break
                        
                                @case(6)
                                    Menunggu hasil seleksi.
                                @break
                        
                                @case(7)
                                    Selesaikan daftar ulang.
                                @break
                        
                                @case(8)
                                    Selamat menjadi peserta didik.
                                @break
                            @endswitch
                        </p>
    
                        </div>
    
                    </div>
    
                    @if($completed)
    
                        <span
                            class="px-3 py-1 rounded-full
                                   bg-emerald-50
                                   text-emerald-600
                                   text-xs font-medium">
    
                            Selesai
    
                        </span>
    
                    @elseif($active)
    
                        <span
                            class="px-3 py-1 rounded-full
                                   bg-[#00A39D]/10
                                   text-[#00A39D]
                                   text-xs font-medium">
    
                            Berjalan
    
                        </span>
    
                    @else
    
                        <span
                            class="px-3 py-1 rounded-full
                                   bg-slate-100
                                   text-slate-500
                                   text-xs font-medium">
    
                            Menunggu
    
                        </span>
    
                    @endif
    
                </div>
    
            @endforeach
    
        </div>
    
    </div>
    </section>
    
    {{-- INFORMASI PPDB --}}
    @if($pengumuman->count())
    
    <section class="px-5 mt-6">
    
        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-4">
    
            <div class="flex items-center gap-3">
    
                <div class="w-10 h-10 rounded-xl bg-teal-50 flex items-center justify-center">
    
                    <x-heroicon-o-megaphone class="w-5 h-5 text-[#00A39D]" />
    
                </div>
    
                <div>
    
                    <div class="text-base font-bold text-slate-900">
                        Informasi PPDB
                    </div>
    
                    <div class="text-xs text-slate-500">
                        Informasi terbaru seputar proses PPDB
                    </div>
    
                </div>
    
            </div>
    
            <a href="{{ route('ppdb.pengumuman') }}"
               class="text-sm font-semibold text-[#00A39D] hover:text-[#008f87] transition">
    
                Semua Informasi →
    
            </a>
    
        </div>
    
        {{-- CARD --}}
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    
            {{-- HEADER --}}
            <div class="px-5 py-4 bg-gradient-to-r from-[#00A39D] to-emerald-400 text-white">
    
                <div class="flex items-center justify-between">
    
                    <div class="flex items-center gap-2 text-sm font-semibold">
    
                        <x-heroicon-o-information-circle class="w-5 h-5"/>
    
                        <span>PPDB Information</span>
    
                    </div>
    
                    <span class="text-xs bg-white/20 px-2 py-1 rounded-lg">
    
                        {{ $pengumuman->count() }} Informasi
    
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
    
                            <x-heroicon-o-megaphone
                                class="w-5 h-5 text-[#00A39D]" />
    
                        </div>
    
                        {{-- CONTENT --}}
                        <div class="flex-1">
    
                            <div class="flex items-start justify-between gap-2">
    
                                <div class="font-semibold text-sm text-slate-900">
    
                                    {{ $item->title }}
    
                                </div> 
                                @if($item->is_pinned)
    
                                    <span class="text-[10px] px-2 py-1 rounded-full bg-red-50 text-red-600 font-semibold">
                                        Penting
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 mt-1 text-xs text-slate-400">
    
                                <x-heroicon-o-calendar-days class="w-3.5 h-3.5"/>
    
                                {{ $item->created_at->translatedFormat('d M Y • H:i') }}
                            </div>
                            <div class="mt-2 text-sm leading-relaxed text-slate-600 line-clamp-2">
    
                                {!! Str::limit(strip_tags($item->content), 120) !!}
                            </div>
    
                            @if($item->attachment)
                                    <a href="{{ asset('storage/'.$item->attachment) }}"
                                   class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-[#00A39D] hover:text-[#008f87] transition">
    
                                    <x-heroicon-o-paper-clip class="w-3.5 h-3.5"/>
                                    Lihat Lampiran
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    
    @endif
    @endsection