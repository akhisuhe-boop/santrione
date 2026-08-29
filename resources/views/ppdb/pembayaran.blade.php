@extends('ppdb.layout.ppdb')

@section('content')

<div class="px-5 pt-4 pb-4 bg-[#F7F9FC] min-h-screen">

    {{-- TOAST ERROR --}}
    @if(session('error'))
        <div
            x-data="{ show:true }"
            x-init="setTimeout(() => show=false,4000)"
            x-show="show"
            x-transition
            class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3">

            <div class="flex items-center gap-3">

                <div class="w-8 h-8 rounded-xl bg-red-100 flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 text-red-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">

                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12A9 9 0 1112 3a9 9 0 019 9z"/>

                    </svg>

                </div>

                <div>

                    <div class="text-sm font-semibold text-red-700">
                        Pembayaran Gagal
                    </div>

                    <div class="text-xs text-red-500">
                        {{ session('error') }}
                    </div>

                </div>

            </div>

        </div>
    @endif

    {{-- HEADER --}}
    <div class="mb-5">

        <h1 class="font-bold text-[17px] text-slate-900">
            Pembayaran PPDB
        </h1>

        <p class="text-[12px] text-slate-500">
            Lakukan pembayaran untuk melanjutkan proses pendaftaran.
        </p>

    </div>

    {{-- DETAIL TAGIHAN --}}
<div
    class="
        relative
        overflow-hidden
        rounded-[24px]
        p-4
        shadow-sm
        border border-[#DCEFED]
        bg-gradient-to-br
        from-white
        via-[#F8FCFC]
        to-[#EEF8F7]
    ">

    {{-- DECORATION --}}
    <div
        class="
            absolute
            -top-12
            -right-12
            w-36
            h-36
            rounded-full
            bg-[#00A39D]/5
        ">
    </div>

    <div class="relative z-10">

        @if($isCicilan)
            @php
                $persentase = $tagihan->nominal > 0
                    ? ($tagihan->nominal_terbayar / $tagihan->nominal) * 100
                    : 0;
            @endphp
        @endif

        {{-- HEADER --}}
        <div class="flex items-center justify-between">

            <div>

                <div
                    class="
                        text-[11px]
                        tracking-wide
                        text-slate-400
                    ">
                    Tagihan PPDB
                </div>

                <h2
                    class="
                        text-[15px]
                        font-bold
                        text-slate-900
                        mt-0.5
                        leading-tight
                    ">

                    {{ $tagihan->judul }}

                </h2>

            </div>

            @if($isCicilan)

                <div
                    class="
                        px-2.5
                        py-0.5
                        rounded-full
                        bg-orange-50
                        border border-orange-100
                        text-orange-500
                        text-[10px]
                        font-semibold
                    ">

                    Cicilan

                </div>

            @endif

        </div>

        {{-- NOMINAL --}}
        <div class="mt-3">

            <div class="text-[11px] text-slate-400">
                Total Tagihan
            </div>

            <div
                class="
                    text-[26px]
                    font-bold
                    text-slate-900
                    mt-0.5
                    leading-none
                ">

                Rp {{ number_format($tagihan->nominal,0,',','.') }}

            </div>

        </div>

        @if($isCicilan)

            {{-- PROGRESS --}}
            <div class="mt-4">

                <div
                    class="
                        flex
                        justify-between
                        items-center
                        mb-1.5
                    ">

                    <span class="text-[11px] text-slate-500">
                        Progress Pembayaran
                    </span>

                    <span
                        class="
                            text-[11px]
                            font-semibold
                            text-[#00A39D]
                        ">

                        {{ round($persentase) }}%

                    </span>

                </div>

                <div
                    class="
                        h-1
                        bg-slate-200/80
                        rounded-full
                        overflow-hidden
                    ">

                    <div
                        class="
                            h-full
                            bg-[#00A39D]
                            rounded-full
                            transition-all
                            duration-500
                        "
                        style="width: {{ $persentase }}%">
                    </div>

                </div>

            </div>

            {{-- STATS --}}
            <div class="grid grid-cols-2 gap-2 mt-3">

                {{-- SUDAH DIBAYAR --}}
                <div
                    class="
                        rounded-xl
                        bg-emerald-50
                        border border-emerald-100
                        px-3
                        py-2.5
                    ">

                    <div
                        class="
                            text-[10px]
                            text-slate-500
                        ">

                        Sudah Dibayar

                    </div>

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-emerald-600
                            mt-0.5
                        ">

                        Rp {{ number_format($tagihan->nominal_terbayar,0,',','.') }}

                    </div>

                </div>

                {{-- SISA TAGIHAN --}}
                <div
                    class="
                        rounded-xl
                        bg-orange-50
                        border border-orange-100
                        px-3
                        py-2.5
                    ">

                    <div
                        class="
                            text-[10px]
                            text-slate-500
                        ">

                        Sisa Tagihan

                    </div>

                    <div
                        class="
                            text-sm
                            font-semibold
                            text-orange-500
                            mt-0.5
                        ">

                        Rp {{ number_format($sisaTagihan,0,',','.') }}

                    </div>

                </div>

            </div>

        @endif

    </div>

</div>

    
        {{-- DOKU --}}
        <div
            class="bg-[#FEFCE8]
                   border border-[#FEF08A]
                   rounded-3xl p-5 mt-4 shadow-sm">
    
            <div class="flex items-center gap-4">
    
                <div
                    class="w-12 h-12 rounded-2xl
                           bg-yellow-100
                           flex items-center justify-center">
    
                    <x-heroicon-o-credit-card
                        class="w-6 h-6 text-yellow-600" />
    
                </div>
    
                <div>
    
                    <h3 class="font-semibold text-slate-900">
                        Payment Gateway
                    </h3>
    
                    <p class="text-xs text-slate-500 mt-1">
                        Bayar instan menggunakan QRIS, Virtual Account, E-Wallet, atau Kartu Debit.
                    </p>
    
                </div>
    
            </div>
    
            <a
            href="{{ route('ppdb.pembayaran.doku.form', $tagihan) }}"
            class="
                mt-5
                flex
                items-center
                justify-center
                w-full
                h-10
                rounded-2xl
                bg-yellow-500
                hover:bg-yellow-600
                text-white
                text-sm
                font-semibold
                transition
            ">
            Bayar Dengan DOKU
        </a>
    
        </div>
    
    </div>

@endsection

