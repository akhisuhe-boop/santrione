@extends('guru.layout.guru')

@section('content')

<div class="p-4 bg-slate-50 min-h-screen">

{{-- ================= HERO PAYROLL ================= --}}

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
        mb-6
    ">

    {{-- ORNAMENT --}}
    <div
        class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/10 -mr-16 -mt-16">
    </div>

    <div
        class="absolute bottom-0 left-0 w-28 h-28 rounded-full bg-white/5 -ml-10 -mb-10">
    </div>

    <div class="relative z-10">

        <div class="flex gap-4">

            {{-- FOTO --}}
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

                    @if($pegawai->foto)

                        <img
                            src="{{ Storage::disk('r2-public')->url($pegawai->foto) }}"
                            alt="{{ $pegawai->nama }}"
                            class="w-full h-full object-cover">

                    @else

                        <div class="w-full h-full flex items-center justify-center">

                            <x-heroicon-o-user class="w-10 h-10 text-white"/>

                        </div>

                    @endif

                </div>

                {{-- STATUS DOT --}}
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

                    Payroll Guru

                </div>

                <div class="text-lg font-bold leading-tight">

                    {{ $pegawai->nama }}

                </div>

                <div class="flex flex-wrap gap-2 mt-2">

                    {{-- STATUS --}}
                    @if($payroll?->status == 'dibayar')

                        <span
                            class="
                                inline-flex items-center gap-1
                                px-3 py-1
                                rounded-full
                                bg-emerald-500/20
                                border border-emerald-300/30
                                text-[11px]
                                font-medium">

                            <x-heroicon-s-check-circle class="w-3.5 h-3.5"/>

                            Sudah Dibayar

                        </span>

                    @elseif($payroll?->status == 'disetujui')

                        <span
                            class="
                                inline-flex items-center gap-1
                                px-3 py-1
                                rounded-full
                                bg-blue-500/20
                                border border-blue-300/30
                                text-[11px]
                                font-medium">

                            <x-heroicon-s-shield-check class="w-3.5 h-3.5"/>

                            Menunggu Pembayaran

                        </span>

                    @else

                        <span
                            class="
                                inline-flex items-center gap-1
                                px-3 py-1
                                rounded-full
                                bg-amber-500/20
                                border border-amber-300/30
                                text-[11px]
                                font-medium">

                            <x-heroicon-s-clock class="w-3.5 h-3.5"/>

                            Draft Payroll

                        </span>

                    @endif

                    {{-- PERIODE --}}
                    <span
                        class="
                            inline-flex items-center gap-1
                            px-3 py-1
                            rounded-full
                            bg-white/15
                            backdrop-blur-sm
                            border border-white/20
                            text-[11px]
                            font-medium">

                        <x-heroicon-o-calendar-days class="w-3.5 h-3.5"/>

                        {{ \Carbon\Carbon::create()->month($payroll?->bulan ?? now()->month)->translatedFormat('F') }}
                        {{ $payroll?->tahun ?? now()->year }}

                    </span>
                    
                </div>
                
            </div>
        </div>
    </div>

</div>

    {{-- ================= RINGKASAN GAJI ================= --}}
<div
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

        <div class="text-base font-semibold text-slate-900">
            Ringkasan Gaji
        </div>

        <div class="text-[13px] text-slate-500 mt-1">
            Rekap payroll periode ini
        </div>

    </div>

    <div class="divide-y divide-slate-100">

        {{-- Pendapatan --}}
        <div class="flex items-center justify-between px-5 py-4">

            <div class="flex items-center gap-3">

                <div
                    class="w-10 h-10 rounded-2xl
                           bg-emerald-50
                           flex items-center justify-center">

                    <x-heroicon-o-banknotes
                        class="w-5 h-5 text-emerald-600"/>

                </div>

                <div>

                    <div class="font-medium text-sm">
                        Pendapatan
                    </div>

                    <div class="text-xs text-slate-500">
                        Total seluruh komponen gaji
                    </div>

                </div>

            </div>

            <div
                class="font-bold text-base text-slate-900">

                Rp {{ number_format($payroll->subtotal ?? 0,0,',','.') }}

            </div>

        </div>

        {{-- Bonus --}}
        <div class="flex items-center justify-between px-5 py-4">

            <div class="flex items-center gap-3">

                <div
                    class="w-10 h-10 rounded-2xl
                           bg-blue-50
                           flex items-center justify-center">

                    <x-heroicon-o-gift
                        class="w-5 h-5 text-blue-600"/>

                </div>

                <div>

                    <div class="font-medium text-sm">
                        Bonus
                    </div>

                    <div class="text-xs text-slate-500">
                        Insentif & penghargaan
                    </div>

                </div>

            </div>

            <div
                class="font-bold text-base text-blue-600">

                + Rp {{ number_format($payroll->bonus ?? 0,0,',','.') }}

            </div>

        </div>

        {{-- Potongan --}}
        <div class="flex items-center justify-between px-5 py-4">

            <div class="flex items-center gap-3">

                <div
                    class="w-10 h-10 rounded-2xl
                           bg-red-50
                           flex items-center justify-center">

                    <x-heroicon-o-minus-circle
                        class="w-5 h-5 text-red-600"/>

                </div>

                <div>

                    <div class="font-medium text-sm">
                        Potongan
                    </div>

                    <div class="text-xs text-slate-500">
                        BPJS, kas dan lainnya
                    </div>

                </div>

            </div>

            <div
                class="font-bold text-base text-red-600">

                - Rp {{ number_format($payroll->potongan ?? 0,0,',','.') }}

            </div>

        </div>

        {{-- Total --}}
        <div class="flex items-center justify-between px-5 py-4">

            <div class="flex items-center gap-3">

                <div
                    class="w-10 h-10 rounded-2xl
                           bg-[#00A39D]/10
                           flex items-center justify-center">

                    <x-heroicon-o-wallet
                        class="w-5 h-5 text-[#00A39D]"/>

                </div>

                <div>

                    <div class="font-semibold text-sm">
                        Total Gaji
                    </div>

                    <div class="text-xs text-slate-500">

                        @if($payroll?->tanggal_bayar)

                            Dibayar
                            {{ \Carbon\Carbon::parse($payroll->tanggal_bayar)->translatedFormat('d M Y') }}

                        @else

                            Belum Dibayarkan

                        @endif

                    </div>

                </div>

            </div>

            <div
                class="text-xl font-bold text-[#00A39D]">

                Rp {{ number_format($payroll->total_gaji ?? 0,0,',','.') }}

            </div>

        </div>

    </div>

</div>

{{-- ================= PENDAPATAN ================= --}}
@php
    $pendapatans = collect($payroll?->items ?? [])->values();
@endphp

<div
    x-data="{ showAllIncome:false }"
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
                    Pendapatan
                </div>

                <div class="text-[13px] text-slate-500 mt-1">
                    Komponen pendapatan guru
                </div>

            </div>

            <div
                class="px-3 py-1 rounded-xl
                       bg-white
                       border border-slate-200
                       text-xs text-slate-600">

                {{ $pendapatans->count() }} Item

            </div>

        </div>

    </div>

    {{-- LIST --}}
    @forelse($pendapatans as $index => $item)

        <div
            x-show="showAllIncome || {{ $index }} < 5"
            x-transition.duration.200ms
            class="
                px-4 py-4
                hover:bg-slate-50
                transition
                {{ !$loop->last ? 'border-b border-slate-100' : '' }}
            ">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3 flex-1">

                    <div
                        class="w-10 h-10
                               rounded-xl
                               bg-emerald-50
                               flex
                               items-center
                               justify-center
                               shrink-0">

                        <x-heroicon-o-banknotes
                            class="w-5 h-5 text-emerald-600"/>

                    </div>

                    <div>

                        <div
                            class="font-semibold
                                   text-sm
                                   text-slate-900">

                            {{ $item->nama_komponen }}

                        </div>

                        <div
                            class="text-sm
                                   text-slate-500
                                   mt-1">

                            {{ $item->qty }} ×
                            Rp {{ number_format($item->tarif,0,',','.') }}

                        </div>

                        @if($item->keterangan)

                            <div
                                class="text-xs
                                       text-slate-400
                                       mt-1">

                                {{ $item->keterangan }}

                            </div>

                        @endif

                    </div>

                </div>

                <div
                    class="text-right
                           shrink-0">

                    <div
                        class="font-bold
                               text-[#00A39D]">

                        Rp {{ number_format($item->subtotal,0,',','.') }}

                    </div>

                </div>

            </div>

        </div>

    @empty

        <div class="p-10">

            <div class="text-center">

                <div
                    class="w-16 h-16
                           rounded-3xl
                           bg-emerald-50
                           mx-auto
                           flex
                           items-center
                           justify-center">

                    <x-heroicon-o-banknotes
                        class="w-8 h-8 text-emerald-500"/>

                </div>

                <div
                    class="font-bold
                           text-slate-900
                           mt-4">

                    Belum Ada Pendapatan

                </div>

                <div
                    class="text-sm
                           text-slate-500
                           mt-2">

                    Komponen pendapatan akan muncul di sini.

                </div>

            </div>

        </div>

    @endforelse

    @if($pendapatans->count() > 5)

        <div
            class="p-4
                   border-t
                   border-slate-100
                   bg-slate-50/50">

            <button
                x-on:click="showAllIncome = !showAllIncome"
                class="
                    w-full
                    py-3
                    rounded-2xl
                    bg-[#00A39D]/10
                    hover:bg-[#00A39D]/20
                    text-[#00A39D]
                    font-medium
                    text-sm
                    transition">

                <span x-show="!showAllIncome">
                    Lihat Semua Pendapatan
                </span>

                <span x-show="showAllIncome">
                    Tampilkan Lebih Sedikit
                </span>

            </button>

        </div>

    @endif

</div>

{{-- ================= BONUS ================= --}}
@php
    $bonus = collect($payroll?->adjustments ?? [])
        ->where('tipe', 'tambahan')
        ->values();
@endphp

<div
    x-data="{ showAllBonus:false }"
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
                    Bonus
                </div>

                <div class="text-[13px] text-slate-500 mt-1">
                    Bonus dan insentif guru
                </div>

            </div>

            <div
                class="px-3 py-1
                       rounded-xl
                       bg-white
                       border border-slate-200
                       text-xs text-slate-600">

                {{ $bonus->count() }} Item

            </div>

        </div>

    </div>

    {{-- LIST --}}
    @forelse($bonus as $index => $item)

        <div
            x-show="showAllBonus || {{ $index }} < 5"
            x-transition.duration.200ms
            class="
                px-4 py-4
                hover:bg-slate-50
                transition
                {{ !$loop->last ? 'border-b border-slate-100' : '' }}
            ">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3 flex-1">

                    <div
                        class="w-10 h-10
                               rounded-xl
                               bg-blue-50
                               flex
                               items-center
                               justify-center
                               shrink-0">

                        <x-heroicon-o-gift
                            class="w-5 h-5 text-blue-600"/>

                    </div>

                    <div>

                        <div
                            class="font-semibold
                                   text-sm
                                   text-slate-900">

                            {{ $item->nama_komponen }}

                        </div>

                        <div
                            class="text-sm
                                   text-slate-500
                                   mt-1">

                            {{ $item->qty }} ×
                            Rp {{ number_format($item->nominal,0,',','.') }}

                        </div>

                        @if($item->catatan)

                            <div
                                class="text-xs
                                       text-slate-400
                                       mt-1">

                                {{ $item->catatan }}

                            </div>

                        @endif

                    </div>

                </div>

                <div class="text-right shrink-0">

                    <div
                        class="font-bold
                               text-blue-600">

                        + Rp {{ number_format($item->subtotal,0,',','.') }}

                    </div>

                </div>

            </div>

        </div>

    @empty

        <div class="p-10">

            <div class="text-center">

                <div
                    class="w-16 h-16
                           rounded-3xl
                           bg-blue-50
                           mx-auto
                           flex
                           items-center
                           justify-center">

                    <x-heroicon-o-gift
                        class="w-8 h-8 text-blue-500"/>

                </div>

                <div
                    class="font-bold
                           text-slate-900
                           mt-4">

                    Belum Ada Bonus

                </div>

                <div
                    class="text-sm
                           text-slate-500
                           mt-2">

                    Bonus akan muncul di sini apabila tersedia.

                </div>

            </div>

        </div>

    @endforelse

    @if($bonus->count() > 5)

        <div
            class="p-4
                   border-t
                   border-slate-100
                   bg-slate-50/50">

            <button
                x-on:click="showAllBonus = !showAllBonus"
                class="
                    w-full
                    py-3
                    rounded-2xl
                    bg-[#00A39D]/10
                    hover:bg-[#00A39D]/20
                    text-[#00A39D]
                    font-medium
                    text-sm
                    transition">

                <span x-show="!showAllBonus">
                    Lihat Semua Bonus
                </span>

                <span x-show="showAllBonus">
                    Tampilkan Lebih Sedikit
                </span>

            </button>

        </div>

    @endif

</div>

{{-- ================= POTONGAN ================= --}}
@php
    $potongan = collect($payroll?->adjustments ?? [])
        ->where('tipe', 'potongan')
        ->values();
@endphp

<div
    x-data="{ showAllPotongan:false }"
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
                    Potongan
                </div>

                <div class="text-[13px] text-slate-500 mt-1">
                    Potongan gaji pada periode payroll
                </div>

            </div>

            <div
                class="px-3 py-1
                       rounded-xl
                       bg-white
                       border border-slate-200
                       text-xs text-slate-600">

                {{ $potongan->count() }} Item

            </div>

        </div>

    </div>

    {{-- LIST --}}
    @forelse($potongan as $index => $item)

        <div
            x-show="showAllPotongan || {{ $index }} < 5"
            x-transition.duration.200ms
            class="
                px-4 py-4
                hover:bg-slate-50
                transition
                {{ !$loop->last ? 'border-b border-slate-100' : '' }}
            ">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3 flex-1">

                    <div
                        class="w-10 h-10
                               rounded-xl
                               bg-red-50
                               flex
                               items-center
                               justify-center
                               shrink-0">

                        <x-heroicon-o-minus-circle
                            class="w-5 h-5 text-red-600"/>

                    </div>

                    <div>

                        <div
                            class="font-semibold
                                   text-sm
                                   text-slate-900">

                            {{ $item->nama_komponen }}

                        </div>

                        <div
                            class="text-sm
                                   text-slate-500
                                   mt-1">

                            {{ $item->qty }} ×
                            Rp {{ number_format($item->nominal,0,',','.') }}

                        </div>

                        @if($item->catatan)

                            <div
                                class="text-xs
                                       text-slate-400
                                       mt-1">

                                {{ $item->catatan }}

                            </div>

                        @endif

                    </div>

                </div>

                <div class="text-right shrink-0">

                    <div
                        class="font-bold
                               text-red-600">

                        - Rp {{ number_format($item->subtotal,0,',','.') }}

                    </div>

                </div>

            </div>

        </div>

    @empty

        <div class="p-10">

            <div class="text-center">

                <div
                    class="w-16 h-16
                           rounded-3xl
                           bg-red-50
                           mx-auto
                           flex
                           items-center
                           justify-center">

                    <x-heroicon-o-minus-circle
                        class="w-8 h-8 text-red-500"/>

                </div>

                <div
                    class="font-bold
                           text-slate-900
                           mt-4">

                    Belum Ada Potongan

                </div>

                <div
                    class="text-sm
                           text-slate-500
                           mt-2">

                    Tidak ada potongan pada periode payroll ini.

                </div>

            </div>

        </div>

    @endforelse

    @if($potongan->count() > 5)

        <div
            class="p-4
                   border-t
                   border-slate-100
                   bg-slate-50/50">

            <button
                x-on:click="showAllPotongan = !showAllPotongan"
                class="
                    w-full
                    py-3
                    rounded-2xl
                    bg-[#00A39D]/10
                    hover:bg-[#00A39D]/20
                    text-[#00A39D]
                    font-medium
                    text-sm
                    transition">

                <span x-show="!showAllPotongan">
                    Lihat Semua Potongan
                </span>

                <span x-show="showAllPotongan">
                    Tampilkan Lebih Sedikit
                </span>

            </button>

        </div>

    @endif

</div>

{{-- ================= RIWAYAT GAJI ================= --}}
@php
    $riwayatPayroll = $riwayatPayroll ?? collect();
@endphp

<div
    x-data="{ showAllPayroll:false }"
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
                    Riwayat Gaji
                </div>

                <div class="text-[13px] text-slate-500 mt-1">
                    Riwayat payroll yang pernah diterima
                </div>

            </div>

            <div
                class="px-3 py-1
                       rounded-xl
                       bg-white
                       border border-slate-200
                       text-xs text-slate-600">

                {{ $riwayatPayroll->count() }} Payroll

            </div>

        </div>

    </div>

    {{-- LIST --}}
    @forelse($riwayatPayroll as $index => $item)
    
        @php
    
            $statusColor = match($item->status){
    
                'dibayar'
                    => 'bg-emerald-50 text-emerald-600',
    
                'disetujui'
                    => 'bg-blue-50 text-blue-600',
    
                'draft'
                    => 'bg-amber-50 text-amber-600',
    
                'cancelled'
                    => 'bg-red-50 text-red-600',
    
                default
                    => 'bg-slate-100 text-slate-600',
    
            };
    
            $statusLabel = match($item->status){
    
                'dibayar'
                    => 'DIBAYAR',
    
                'disetujui'
                    => 'DISETUJUI',
    
                'draft'
                    => 'DRAFT',
    
                'cancelled'
                    => 'DIBATALKAN',
    
                default
                    => strtoupper($item->status),
    
            };
    
        @endphp
    
        <div
            x-show="showAllPayroll || {{ $index }} < 5"
            x-transition.duration.200ms
            class="
                px-4 py-3
                hover:bg-slate-50
                transition
                {{ !$loop->last ? 'border-b border-slate-100' : '' }}
            ">
    
            <div class="flex items-center justify-between">
    
                <div class="flex gap-3">
    
                    {{-- ICON --}}
                    <div
                        class="
                            w-10 h-10
                            rounded-xl
                            bg-[#00A39D]/10
                            flex
                            items-center
                            justify-center
                            shrink-0
                        ">
    
                        <x-heroicon-o-banknotes
                            class="w-5 h-5 text-[#00A39D]" />
    
                    </div>
    
                    {{-- CONTENT --}}
                    <div>
    
                        {{-- PERIODE --}}
                        <div
                            class="
                                font-semibold
                                text-sm
                                text-slate-900
                            ">
    
                            {{
                                \Carbon\Carbon::create()
                                    ->month($item->bulan)
                                    ->translatedFormat('F')
                            }}
                            {{ $item->tahun }}
    
                        </div>
    
                        {{-- TOTAL --}}
                        <div
                            class="
                                text-sm
                                text-slate-500
                                mt-1
                            ">
    
                            Total Gaji
    
                            <span class="font-semibold text-slate-700">
    
                                Rp {{ number_format($item->total_gaji,0,',','.') }}
    
                            </span>
    
                        </div>
    
                        {{-- TANGGAL / STATUS --}}
                        <div
                            class="
                                flex
                                items-center
                                gap-1
                                text-xs
                                text-slate-400
                                mt-2
                            ">
    
                            <x-heroicon-o-calendar class="w-3 h-3"/>
    
                            @switch($item->status)
    
                                @case('dibayar')
    
                                    Dibayar
    
                                    @if($item->tanggal_bayar)
    
                                        •
                                        {{
                                            \Carbon\Carbon::parse($item->tanggal_bayar)
                                                ->translatedFormat('d F Y')
                                        }}
    
                                    @endif
    
                                    @break
    
                                @case('disetujui')
    
                                    Menunggu Pembayaran
    
                                    @break
    
                                @case('draft')
    
                                    Belum Dibayarkan
    
                                    @break
    
                                @case('cancelled')
    
                                    Payroll Dibatalkan
    
                                    @break
    
                                @default
    
                                    -
    
                            @endswitch
    
                        </div>
    
                    </div>
    
                </div>
    
                {{-- BADGE --}}
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
    
                        {{ $statusLabel }}
    
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
                        bg-[#00A39D]/10
                        mx-auto
                        flex
                        items-center
                        justify-center
                    ">
    
                    <x-heroicon-o-banknotes
                        class="w-8 h-8 text-[#00A39D]"/>
    
                </div>
    
                <div
                    class="
                        font-bold
                        text-slate-900
                        mt-4
                    ">
    
                    Belum Ada Riwayat Gaji
    
                </div>
    
                <div
                    class="
                        text-sm
                        text-slate-500
                        mt-2
                    ">
    
                    Riwayat pembayaran gaji akan muncul di sini.
    
                </div>
    
            </div>
    
        </div>
    
    @endforelse

    @if($riwayatPayroll->count() > 5)

        <div
            class="
                p-4
                border-t
                border-slate-100
                bg-slate-50/50
            ">

            <button
                x-on:click="
                    showAllPayroll =
                    !showAllPayroll
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

                <span x-show="!showAllPayroll">
                    Lihat Semua Riwayat Gaji
                </span>

                <span x-show="showAllPayroll">
                    Tampilkan Lebih Sedikit
                </span>

            </button>

        </div>

    @endif

</div>
    
    </div>

@endsection