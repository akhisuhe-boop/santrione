@extends('wali.layout.wali')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-[#F6F8FB] via-white to-[#EEF7F6]">

    <div class="max-w-lg mx-auto px-4 py-8">

        @if(session('error'))
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-5 py-4 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">

            <div class="relative p-5 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-[#056B66] via-[#0FA6A1] to-[#14B8B0]"></div>
                <div class="relative">
                    <div class="text-xl font-semibold text-white">Metode Pembayaran</div>
                    <div class="text-sm text-white/80 mt-1">Pilih salah satu metode di bawah</div>
                </div>
            </div>

            <div class="p-5 space-y-3">

                <form method="POST" action="{{ route('wali.pembayaran.doku', $tagihan) }}">
                    @csrf
                    <input type="hidden" name="payment_method" value="VA">
                    <button type="submit" class="w-full flex items-center gap-4 p-4 rounded-2xl border border-slate-200 hover:border-[#00A39D] hover:bg-[#00A39D]/5 transition text-left">
                        <div class="w-12 h-12 rounded-2xl bg-blue-100 flex items-center justify-center shrink-0">
                            <x-heroicon-o-building-library class="w-6 h-6 text-blue-600" />
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-slate-900">Virtual Account</div>
                            <div class="text-xs text-slate-500 mt-0.5">Transfer dari bank/e-wallet manapun</div>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-300" />
                    </button>
                </form>

                <form method="POST" action="{{ route('wali.pembayaran.doku', $tagihan) }}">
                    @csrf
                    <input type="hidden" name="payment_method" value="QRIS">
                    <button type="submit" class="w-full flex items-center gap-4 p-4 rounded-2xl border border-slate-200 hover:border-[#00A39D] hover:bg-[#00A39D]/5 transition text-left">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center shrink-0">
                            <x-heroicon-o-qr-code class="w-6 h-6 text-amber-600" />
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-slate-900">QRIS</div>
                            <div class="text-xs text-slate-500 mt-0.5">Scan pakai e-wallet/m-banking apapun</div>
                        </div>
                        <x-heroicon-o-chevron-right class="w-5 h-5 text-slate-300" />
                    </button>
                </form>

            </div>

        </div>

        <p class="text-center text-xs text-slate-400 mt-4">
            Secure Checkout &bull; Encrypted &bull; DOKU Gateway
        </p>

    </div>

</div>

@endsection
