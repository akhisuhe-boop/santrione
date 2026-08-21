@extends('wali.layout.wali')

@section('content')

<div class="min-h-screen bg-gradient-to-b from-slate-50 to-white">

    <div class="max-w-lg mx-auto px-4 py-6">

        @if(session('error'))
            <div class="mb-5 rounded-2xl bg-red-50 border border-red-100 text-red-600 px-4 py-3.5 text-sm font-medium flex items-start gap-2.5">
                <x-heroicon-o-exclamation-circle class="w-5 h-5 shrink-0 mt-0.5" />
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="rounded-2xl bg-slate-900 text-white p-4 mb-4 flex items-center justify-between shadow-sm">
            <div class="text-sm text-slate-300">Nominal Top Up</div>
            <div class="text-xl font-bold">Rp {{ number_format($amount, 0, ',', '.') }}</div>
        </div>

        <div class="rounded-3xl overflow-hidden shadow-sm border border-slate-200/80">

            <div class="relative overflow-hidden bg-gradient-to-br from-[#0B9A93] via-[#0FA6A1] to-[#17C3B8] p-5">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-10 -left-6 w-28 h-28 bg-black/10 rounded-full blur-2xl"></div>
                <div class="relative">
                    <h1 class="text-xl font-bold text-white">Metode Pembayaran</h1>
                    <p class="text-sm text-white/80 mt-0.5">Pilih salah satu metode di bawah</p>
                </div>
            </div>

            <div class="bg-white divide-y divide-slate-100">

            {{-- VIRTUAL ACCOUNT --}}
            <button type="button" onclick="toggleVA()"
                class="w-full flex items-center gap-3.5 p-4 hover:bg-slate-50/80 transition-colors text-left">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <x-heroicon-o-building-library class="w-5 h-5 text-blue-500" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-900 text-sm">Virtual Account</div>
                    <div class="text-xs text-slate-400 mt-0.5">Pilih bank Anda</div>
                </div>
                <x-heroicon-o-chevron-down id="va-chevron" class="w-4 h-4 text-slate-300 transition-transform shrink-0" />
            </button>

            <div id="va-bank-list" class="hidden bg-slate-50/60 px-4 py-3">
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['BCA' => 'bca', 'BNI' => 'bni', 'BRI' => 'bri', 'BSI' => 'bsi', 'MANDIRI' => 'mandiri', 'BJB' => 'bjb'] as $kode => $file)
                        <form method="POST" action="{{ route('wali.topup.store') }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="VA">
                            <input type="hidden" name="bank" value="{{ $kode }}">
                            <button type="submit" class="w-full flex items-center justify-center p-2.5 h-14 rounded-xl bg-white border border-slate-200/80 hover:border-[#00A39D]/40 hover:shadow-sm transition-all">
                                <img src="{{ asset('images/payment-logos/' . $file . '.png') }}" alt="{{ $kode }}" class="max-h-8 max-w-[80%] object-contain">
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

            {{-- QRIS --}}
            <form method="POST" action="{{ route('wali.topup.store') }}">
                @csrf
                <input type="hidden" name="payment_method" value="QRIS">
                <button type="submit" class="w-full flex items-center gap-3.5 p-4 hover:bg-slate-50/80 transition-colors text-left">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/payment-logos/qris.png') }}" alt="QRIS" class="h-5 object-contain">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-900 text-sm">QRIS</div>
                        <div class="text-xs text-slate-400 mt-0.5">Scan pakai e-wallet/m-banking apapun</div>
                    </div>
                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0" />
                </button>
            </form>

            {{-- E-WALLET --}}
            <form method="POST" action="{{ route('wali.topup.store') }}">
                @csrf
                <input type="hidden" name="payment_method" value="DANA">
                <button type="submit" class="w-full flex items-center gap-3.5 p-4 hover:bg-slate-50/80 transition-colors text-left">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/payment-logos/dana.png') }}" alt="DANA" class="h-5 object-contain">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-900 text-sm">DANA</div>
                        <div class="text-xs text-slate-400 mt-0.5">Bayar lewat aplikasi DANA</div>
                    </div>
                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0" />
                </button>
            </form>

            <form method="POST" action="{{ route('wali.topup.store') }}">
                @csrf
                <input type="hidden" name="payment_method" value="SHOPEEPAY">
                <button type="submit" class="w-full flex items-center gap-3.5 p-4 hover:bg-slate-50/80 transition-colors text-left">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/payment-logos/shopeepay.png') }}" alt="ShopeePay" class="h-5 object-contain">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-900 text-sm">ShopeePay</div>
                        <div class="text-xs text-slate-400 mt-0.5">Bayar lewat aplikasi Shopee</div>
                    </div>
                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0" />
                </button>
            </form>

            <button type="button" onclick="document.getElementById('ovo-modal').classList.remove('hidden')"
                class="w-full flex items-center gap-3.5 p-4 hover:bg-slate-50/80 transition-colors text-left">
                <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/payment-logos/ovo.png') }}" alt="OVO" class="h-5 object-contain">
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-900 text-sm">OVO</div>
                    <div class="text-xs text-slate-400 mt-0.5">Approve dari aplikasi OVO Anda</div>
                </div>
                <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0" />
            </button>

            {{-- MINIMARKET --}}
            <form method="POST" action="{{ route('wali.topup.store') }}">
                @csrf
                <input type="hidden" name="payment_method" value="ALFAMART">
                <button type="submit" class="w-full flex items-center gap-3.5 p-4 hover:bg-slate-50/80 transition-colors text-left">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/payment-logos/alfamart.png') }}" alt="Alfamart" class="h-5 object-contain">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-900 text-sm">Alfamart</div>
                        <div class="text-xs text-slate-400 mt-0.5">Bayar tunai di kasir Alfamart</div>
                    </div>
                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0" />
                </button>
            </form>

            <form method="POST" action="{{ route('wali.topup.store') }}">
                @csrf
                <input type="hidden" name="payment_method" value="INDOMARET">
                <button type="submit" class="w-full flex items-center gap-3.5 p-4 hover:bg-slate-50/80 transition-colors text-left">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/payment-logos/indomaret.png') }}" alt="Indomaret" class="h-5 object-contain">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-900 text-sm">Indomaret</div>
                        <div class="text-xs text-slate-400 mt-0.5">Bayar tunai di kasir Indomaret</div>
                    </div>
                    <x-heroicon-o-chevron-right class="w-4 h-4 text-slate-300 shrink-0" />
                </button>
            </form>

            </div>
        </div>

        <div class="flex items-center justify-center gap-1.5 mt-6 text-[11px] text-slate-400">
            <x-heroicon-o-shield-check class="w-3.5 h-3.5" />
            <span>Secure Checkout &bull; Encrypted &bull; DOKU Gateway</span>
        </div>

    </div>

</div>

{{-- MODAL INPUT NOMOR HP OVO --}}
<div id="ovo-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-3xl p-6 w-full max-w-sm shadow-xl">
        <div class="text-lg font-bold text-slate-900 mb-1">Bayar dengan OVO</div>
        <p class="text-xs text-slate-500 mb-4 leading-relaxed">
            Masukkan nomor HP yang terdaftar di OVO. Notifikasi approve pembayaran akan muncul di aplikasi OVO Anda (proses bisa memakan waktu sampai 1 menit).
        </p>
        <form method="POST" action="{{ route('wali.topup.store') }}">
            @csrf
            <input type="hidden" name="payment_method" value="OVO">
            <input type="tel" name="ovo_phone" placeholder="08123456789" required minlength="9" maxlength="15"
                   class="w-full border border-slate-200 rounded-xl p-3 mb-4 text-sm focus:ring-2 focus:ring-[#00A39D]/30 focus:border-[#00A39D] outline-none transition">
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('ovo-modal').classList.add('hidden')"
                        class="flex-1 h-11 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
                    Batal
                </button>
                <button type="submit" class="flex-1 h-11 rounded-xl bg-[#00A39D] hover:bg-[#008f89] text-white text-sm font-semibold transition">
                    Kirim Approval
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleVA() {
    document.getElementById('va-bank-list').classList.toggle('hidden');
    document.getElementById('va-chevron').classList.toggle('rotate-180');
}
</script>

@endsection
