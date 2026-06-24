@extends('wali.layout.wali')

@section('content')

<div class="p-4 bg-[#F7F9FC] min-h-screen">

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

        <h1 class="font-bold text-[18px] text-slate-900">
            Pembayaran Tagihan
        </h1>

        <p class="text-[12px] text-slate-500">
            Pilih metode pembayaran
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
                        text-[10px]
                        tracking-wide
                        text-slate-400
                    ">
                    Tagihan
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

    {{-- WALLET --}}
    <div
        class="bg-[#ECFDF5]
               border border-[#D1FAE5]
               rounded-3xl
               p-5 mt-4 shadow-sm">

        <div class="flex items-center gap-4">

            <div
                class="w-12 h-12 rounded-2xl
                       bg-[#00A39D]/10
                       flex items-center justify-center">

                <x-heroicon-o-wallet
                    class="w-6 h-6 text-[#00A39D]" />

            </div>

            <div class="flex-1">

                <h3 class="font-semibold text-slate-900">
                    Saldo Santri
                </h3>

                <p class="text-xs text-slate-500 mt-1">
                    Bayar menggunakan saldo
                </p>

            </div>

            <div class="text-right">

                <div class="text-xs text-slate-400">
                    Saldo
                </div>

                <div class="font-bold text-[#00A39D]">
                    Rp {{ number_format($siswa->wallet->saldo ?? 0,0,',','.') }}
                </div>

            </div>

        </div>

        <form
            method="POST"
            action="{{ route('wali.pembayaran.saldo',$tagihan) }}"
            class="mt-5">

            @csrf

            @if($isCicilan)

                <div class="mb-4">

                    <div class="flex justify-between items-center mb-2">

                        <span class="text-sm font-medium text-slate-700">
                            Nominal Pembayaran
                        </span>

                        <span class="text-xs text-slate-500">
                            Sisa Rp {{ number_format($sisaTagihan,0,',','.') }}
                        </span>

                    </div>

                    <div class="relative">

                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-medium">

                            Rp

                        </span>

                        <input
                            id="nominal"
                            type="number"
                            name="nominal"
                            value="{{ old('nominal', $sisaTagihan) }}"
                            min="1000"
                            max="{{ $sisaTagihan }}"
                            required
                            class="w-full pl-12 pr-4 py-3 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-[#00A39D] focus:border-[#00A39D]">

                    </div>
                </div>

            @endif

            <button
                class="w-full
                       bg-[#00A39D]
                       hover:bg-[#008f89]
                       text-white
                       h-10
                       py-3
                       text-sm
                       rounded-2xl
                       font-semibold">

                Bayar Dengan Saldo

            </button>

        </form>

    </div>

    {{-- TRANSFER --}}
<div
    class="bg-[#EFF6FF]
           border border-[#DBEAFE]
           rounded-3xl p-5 mt-4 shadow-sm">

    <div class="flex items-center gap-4">

        <div
            class="w-12 h-12 rounded-2xl
                   bg-blue-100
                   flex items-center justify-center">

            <x-heroicon-o-building-library
                class="w-6 h-6 text-blue-600" />

        </div>

        <div class="flex-1">

            <h3 class="font-semibold text-slate-900">
                Transfer Bank
            </h3>

            <p class="text-xs text-slate-500 mt-1">
                Lihat rekening tujuan dan upload bukti transfer
            </p>

        </div>

    </div>

    <a
        href="{{ route('wali.pembayaran.transfer', $tagihan) }}"
        class="
            mt-5
            flex
            items-center
            justify-center
            w-full
            h-10
            rounded-2xl
            bg-blue-600
            hover:bg-blue-700
            text-white
            text-sm
            font-semibold
            transition
        ">
        Bayar Via Transfer
    </a>

</div>

    {{-- DUITKU --}}
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
                    QRIS, Gopay, Dana, Shopeepay, Virtual Account
                </p>

            </div>

        </div>

        <a
        href="{{ route('wali.pembayaran.duitku.form', $tagihan) }}"
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
        Bayar Dengan Duitku
    </a>

    </div>

</div>

@endsection

