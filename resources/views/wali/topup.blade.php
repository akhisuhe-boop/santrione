@extends('wali.layout.wali')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-slate-100 p-6">

    <div class="max-w-2xl mx-auto">

        {{-- 💳 WALLET CARD --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 to-teal-500 text-white p-6 shadow-lg mb-6">

        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>
        <div class="absolute bottom-0 right-0 w-32 h-32 bg-white/10 rounded-full"></div>

        <div class="flex items-center gap-2 text-white/80 text-sm">
            <x-heroicon-o-credit-card class="w-5 h-5" />
            <span>Saldo Aktif</span>
        </div>

        {{-- ✅ PAKAI WALLET (SAMA SEPERTI KEUANGAN) --}}
        <h1 class="text-3xl font-bold mt-2">
            Rp {{ number_format($wallet->saldo ?? 0,0,',','.') }}
        </h1>

        <div class="flex items-center gap-2 mt-2 text-white/80 text-xs">
            <x-heroicon-o-bolt class="w-4 h-4" />
            <span>Top up instan & otomatis masuk</span>
        </div>

    </div>

        {{-- HEADER --}}
        <div class="mb-5">
            <h2 class="text-xl font-bold text-slate-900">Top Up Saldo</h2>
            <p class="text-slate-500 text-sm">Pilih nominal atau masukkan sendiri</p>
        </div>

        <form method="POST" action="{{ route('wali.topup.pilih-nominal') }}">
            @csrf

            {{-- QUICK AMOUNT --}}
            <div class="grid grid-cols-3 gap-3 mb-6">

                @php
                    $nominals = [25000, 50000, 75000, 100000, 200000, 300000];
                @endphp

                @foreach($nominals as $nominal)
                <label class="cursor-pointer">
                    <input type="radio" name="amount" value="{{ $nominal }}" class="peer hidden">

                    <div class="
                        rounded-2xl border border-slate-200 bg-white p-3 text-center
                        hover:shadow-md hover:border-emerald-400 transition-all
                        peer-checked:bg-emerald-500 peer-checked:text-white
                        peer-checked:border-emerald-500
                    ">

                        <div class="flex justify-center mb-1">
                            <x-heroicon-o-banknotes class="w-5 h-5 opacity-70 peer-checked:opacity-100" />
                        </div>

                        <div class="text-xs opacity-70 peer-checked:opacity-90">Top Up</div>

                        <div class="font-bold text-sm mt-1">
                            Rp {{ number_format($nominal,0,',','.') }}
                        </div>

                    </div>
                </label>
                @endforeach

            </div>

            {{-- CUSTOM AMOUNT --}}
            <div class="mb-6">
                <label class="text-sm font-medium text-slate-700 flex items-center gap-2">
                    <x-heroicon-o-pencil-square class="w-4 h-4 text-slate-500" />
                    Nominal lain
                </label>

                <div class="mt-2 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        Rp
                    </span>

                    <input
                        type="number"
                        name="custom_amount"
                        placeholder="0"
                        class="w-full pl-10 pr-4 py-3 rounded-2xl border border-slate-200
                               focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400
                               outline-none transition"
                    >
                </div>

                <div class="flex items-center gap-2 text-xs text-slate-400 mt-1">
                    <x-heroicon-o-information-circle class="w-4 h-4" />
                    <span>Jika diisi akan menggantikan pilihan nominal</span>
                </div>
            </div>

            {{-- BUTTON --}}
            <button type="submit"
                class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500
                       text-white font-semibold shadow-lg
                       flex items-center justify-center gap-2
                       hover:scale-[1.01] active:scale-95 transition">

                <x-heroicon-o-arrow-right-circle class="w-5 h-5" />
                Lanjutkan Pembayaran
            </button>

        </form>
    </div>
</div>

@endsection