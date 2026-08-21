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

            <div class="p-5 space-y-5">

                {{-- VA & QRIS --}}
                <div class="space-y-3">
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

                {{-- E-WALLET --}}
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2 px-1">E-Wallet</div>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['DANA' => 'DANA', 'SHOPEEPAY' => 'ShopeePay', 'OVO' => 'OVO'] as $val => $label)
                            @if($val === 'OVO')
                                <button type="button" onclick="document.getElementById('ovo-modal').classList.remove('hidden')"
                                    class="flex flex-col items-center gap-1 p-3 rounded-2xl border border-slate-200 hover:border-[#00A39D] hover:bg-[#00A39D]/5 transition">
                                    <span class="text-xs font-semibold text-slate-700">{{ $label }}</span>
                                </button>
                            @else
                                <form method="POST" action="{{ route('wali.pembayaran.doku', $tagihan) }}">
                                    @csrf
                                    <input type="hidden" name="payment_method" value="{{ $val }}">
                                    <button type="submit" class="w-full flex flex-col items-center gap-1 p-3 rounded-2xl border border-slate-200 hover:border-[#00A39D] hover:bg-[#00A39D]/5 transition">
                                        <span class="text-xs font-semibold text-slate-700">{{ $label }}</span>
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- MINIMARKET --}}
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2 px-1">Minimarket</div>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['ALFAMART' => 'Alfamart', 'INDOMARET' => 'Indomaret'] as $val => $label)
                            <form method="POST" action="{{ route('wali.pembayaran.doku', $tagihan) }}">
                                @csrf
                                <input type="hidden" name="payment_method" value="{{ $val }}">
                                <button type="submit" class="w-full flex items-center justify-center gap-2 p-3 rounded-2xl border border-slate-200 hover:border-[#00A39D] hover:bg-[#00A39D]/5 transition">
                                    <span class="text-xs font-semibold text-slate-700">{{ $label }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

            </div>

        </div>

        <p class="text-center text-xs text-slate-400 mt-4">
            Secure Checkout &bull; Encrypted &bull; DOKU Gateway
        </p>

    </div>

</div>

{{-- MODAL INPUT NOMOR HP OVO -- beda alur dari yang lain, DOKU push
     notifikasi ke app OVO nomor ini, wali approve dari HP-nya sendiri --}}
<div id="ovo-modal" class="hidden fixed inset-0 bg-black/40 flex items-end sm:items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl p-6 w-full max-w-sm">
        <div class="text-lg font-semibold text-slate-900 mb-1">Bayar dengan OVO</div>
        <p class="text-xs text-slate-500 mb-4">
            Masukkan nomor HP yang terdaftar di OVO. Notifikasi approve pembayaran akan muncul di aplikasi OVO Anda (proses bisa memakan waktu sampai 1 menit).
        </p>
        <form method="POST" action="{{ route('wali.pembayaran.doku', $tagihan) }}">
            @csrf
            <input type="hidden" name="payment_method" value="OVO">
            <input type="tel" name="ovo_phone" placeholder="08123456789" required minlength="9" maxlength="15"
                   class="w-full border border-slate-200 rounded-2xl p-3 mb-4">
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('ovo-modal').classList.add('hidden')"
                        class="flex-1 h-11 rounded-2xl border border-slate-200 text-slate-600 text-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="flex-1 h-11 rounded-2xl bg-[#00A39D] text-white text-sm font-semibold">
                    Kirim Approval
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
