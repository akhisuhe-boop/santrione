@extends('wali.layout.wali')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-[#F6F8FB] via-white to-[#EEF7F6]">

    <div class="max-w-5xl mx-auto px-4 py-8">

        @if(session('error'))
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 px-5 py-4 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-6 flex items-start gap-3 rounded-2xl bg-blue-50 border border-blue-100 px-5 py-4">
            <x-heroicon-o-shield-check class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
            <p class="text-xs text-blue-700 leading-relaxed">
                Setelah memilih metode, Anda akan diarahkan ke halaman pembayaran resmi
                <strong>DOKU</strong> &mdash; partner payment gateway resmi Qinara yang berlisensi
                Bank Indonesia. Nama <strong>Qinara</strong> akan tetap tampil sebagai penerima
                pembayaran di halaman tersebut.
            </p>
        </div>

        <form method="POST" action="{{ route('wali.pembayaran.doku', $tagihan) }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- RIGHT: PAYMENT METHODS --}}
                <div class="lg:col-span-2">

                    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">

                        {{-- HEADER --}}
                        <div class="relative p-5 border-b border-white/10 overflow-hidden">

                            {{-- DARK PRIMARY GRADIENT BACKGROUND --}}
                            <div class="absolute inset-0 bg-gradient-to-r from-[#056B66] via-[#0FA6A1] to-[#14B8B0]"></div>

                            {{-- SOFT GLOW EFFECT --}}
                            <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 blur-3xl rounded-full"></div>
                            <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-black/10 blur-3xl rounded-full"></div>

                            {{-- CONTENT --}}
                            <div class="relative">

                                <div class="text-xl font-semibold text-white">
                                    Metode Pembayaran
                                </div>

                                <div class="text-sm text-white/80 mt-1">
                                    Pilih salah satu metode yang tersedia
                                </div>

                            </div>

                        </div>

                        @php
                            $grouped = collect($paymentMethods)->groupBy('category');

                            $logos = [

                                'BCA'       => asset('storage/logo-payment/bca.png'),
                                'BNI'       => asset('storage/logo-payment/bni.png'),
                                'BRI'       => asset('storage/logo-payment/bri.png'),
                                'MANDIRI'   => asset('storage/logo-payment/mandiri.png'),
                                'BSI'       => asset('storage/logo-payment/bsi.png'),

                                'OV'        => asset('storage/logo-payment/ovo.png'),
                                'DA'        => asset('storage/logo-payment/dana.png'),
                                'SP'        => asset('storage/logo-payment/shopeepay.png'),

                                'QRIS'      => asset('storage/logo-payment/qris.png'),

                                'ALFAMART'  => asset('storage/logo-payment/alfamart.png'),
                                'INDOMARET' => asset('storage/logo-payment/indomaret.png'),

                            ];

                            $fallbackLogo = asset('storage/logo-payment/default.png');

                            @endphp

                        <div class="divide-y divide-slate-100">

                            @foreach($grouped as $category => $methods)

                                {{-- CATEGORY LABEL --}}
                                <div class="relative px-5 py-3 overflow-hidden">

                                    {{-- SOFT PRIMARY GRADIENT --}}
                                    <div class="absolute inset-0 bg-gradient-to-r from-[#0FA6A1]/15 via-[#0FA6A1]/10 to-[#0B7C77]/15"></div>

                                    {{-- VERY SOFT GLOW --}}
                                    <div class="absolute -top-6 -right-6 w-20 h-20 bg-[#0FA6A1]/10 blur-2xl rounded-full"></div>

                                    {{-- TEXT --}}
                                    <div class="relative text-[11px] font-semibold uppercase tracking-widest text-[#0F766E]">
                                        {{ $category }}
                                    </div>

                                </div>

                                @foreach($methods as $method)

                                    <label class="block cursor-pointer group">

                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="{{ $method['code'] }}"
                                            class="peer hidden"
                                            required
                                        >

                                        <div class="
                                        flex items-center gap-4 px-5 py-4
                                        transition-all duration-300 ease-out

                                        group-hover:bg-slate-50/60

                                        peer-checked:bg-gradient-to-r
                                        peer-checked:from-[#0FA6A1]/10
                                        peer-checked:via-[#0FA6A1]/5
                                        peer-checked:to-transparent

                                        peer-checked:shadow-[inset_3px_0px_0px_#0FA6A1]
                                        peer-checked:scale-[1.01]

                                        active:scale-[0.995]
                                    ">

                                            {{-- LOGO --}}
                                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center overflow-hidden">
                                                <img
                                                    src="{{ $logos[$method['code']] ?? $fallbackLogo }}"
                                                    class="w-9 h-9 object-contain"
                                                    alt="{{ $method['name'] }}"
                                                    loading="lazy"
                                                    onerror="this.onerror=null;this.src='{{ $fallbackLogo }}';"
                                                />
                                            </div>

                                            {{-- TEXT --}}
                                            <div class="flex-1">
                                                <div class="text-sm font-semibold text-slate-900">
                                                    {{ $method['name'] }}
                                                </div>
                                                <div class="text-xs text-slate-400">
                                                    Instant payment • Auto verified
                                                </div>
                                            </div>

                                            {{-- SELECT INDICATOR --}}
                                            <div class="opacity-0 peer-checked:opacity-100 transition">
                                                <div class="w-6 h-6 rounded-full bg-[#0FA6A1] flex items-center justify-center text-white text-xs">
                                                    ✓
                                                </div>
                                            </div>

                                        </div>

                                    </label>

                                @endforeach

                            @endforeach

                        </div>

                    </div>

                    {{-- CTA --}}
                    <button
                        type="submit"
                        class="
                            w-full mt-5 py-4 rounded-2xl font-semibold text-white
                            bg-gradient-to-r from-[#0FA6A1] to-[#008E88]
                            shadow-md hover:shadow-xl
                            transition-all active:scale-[0.99]
                        "
                    >
                        Lanjutkan Pembayaran
                    </button>

                    <p class="text-[11px] text-center text-slate-400 mt-3">
                        Secure Checkout • Encrypted • DOKU Gateway
                    </p>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection