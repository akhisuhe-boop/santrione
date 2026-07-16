@extends('ppdb.layout.ppdb')

@section('content')

<div class="max-w-[430px] mx-auto px-4 pb-24">

    {{-- HEADER --}}
    <div class="mt-3 pl-1">

    <h1 class="text-base font-bold leading-tight text-slate-900">
        Transfer Pembayaran PPDB
    </h1>

    <p class="mt-0.5 text-xs text-slate-500 leading-5">
        Transfer sesuai nominal dan upload bukti pembayaran.
    </p>

    </div>
    
    @if(session('success'))

        <div
            class="mt-4
                   rounded-2xl
                   border
                   border-emerald-200
                   bg-emerald-50
                   p-4">
    
            <div class="flex items-start gap-3">
    
                <div
                    class="w-10
                           h-10
                           rounded-xl
                           bg-emerald-100
                           flex
                           items-center
                           justify-center">
    
                    <x-heroicon-o-check-circle
                        class="w-6 h-6 text-emerald-600"/>
    
                </div>
    
                <div>
    
                    <div class="text-sm font-semibold text-emerald-700">
                        Berhasil
                    </div>
    
                    <div class="text-xs text-emerald-600 mt-1">
                        {{ session('success') }}
                    </div>
    
                </div>
    
            </div>
    
        </div>
    
    @endif

    {{-- DETAIL TAGIHAN --}}
    <div
        class="mt-3 rounded-3xl bg-white border border-slate-200 p-5 shadow-sm">

        <div class="flex justify-between">

            <span class="text-slate-500">
                Tagihan
            </span>

            <span class="font-medium text-right">
                {{ $tagihan->judul }}
            </span>

        </div>

        <div class="flex justify-between mt-3">

            <span class="text-slate-500">
                Sisa Tagihan
            </span>

            <span class="font-bold text-[#00A39D]">
                Rp {{ number_format($sisaTagihan,0,',','.') }}
            </span>

        </div>

        <div class="flex justify-between mt-3">

            <span class="text-slate-500">
                Status
            </span>

            @php
            $statusColor = match($tagihan->status) {
                'belum' => 'bg-amber-100 text-amber-700',
                'menunggu_verifikasi' => 'bg-blue-100 text-blue-700',
                'lunas' => 'bg-emerald-100 text-emerald-700',
                default => 'bg-slate-100 text-slate-700',
            };
            @endphp
            
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                {{ strtoupper(str_replace('_',' ', $tagihan->status)) }}
            </span>

        </div>

    </div>

    {{-- REKENING TUJUAN --}}
<div class="mt-5 rounded-3xl bg-white border border-slate-200 p-4 shadow-sm">

    <h3 class="font-semibold text-slate-900 mb-4">
        Rekening Tujuan
    </h3>

    @php
    $rekening = $tagihan->rekening;

    $bankLogos = [
        'BCA'      => asset('storage/logo-payment/bca.png'),
        'BNI'      => asset('storage/logo-payment/bni.png'),
        'BRI'      => asset('storage/logo-payment/bri.png'),
        'MANDIRI'  => asset('storage/logo-payment/mandiri.png'),
        'BSI'      => asset('storage/logo-payment/bsi.png'),
    ];

    $fallbackLogo = asset('storage/logo-payment/default.png');

    $logo = $fallbackLogo;

    if ($rekening) {

        $bank = strtoupper($rekening->bank);

        if (str_contains($bank, 'BCA')) {
            $logo = $bankLogos['BCA'];
        }

        elseif (str_contains($bank, 'BNI')) {
            $logo = $bankLogos['BNI'];
        }

        elseif (str_contains($bank, 'BRI')) {
            $logo = $bankLogos['BRI'];
        }

        elseif (str_contains($bank, 'MANDIRI')) {
            $logo = $bankLogos['MANDIRI'];
        }

        elseif (
            str_contains($bank, 'BSI') ||
            str_contains($bank, 'SYARIAH')
        ) {
            $logo = $bankLogos['BSI'];
        }
    }
@endphp

    @if($rekening)

        <div class="border border-slate-100 rounded-2xl p-3 bg-slate-50">

            <div class="flex items-center justify-between gap-3">

                <div class="flex items-center gap-3 flex-1">

                    {{-- LOGO BANK --}}
                    <div
                        class="
                            w-12 h-12
                            rounded-xl
                            bg-white
                            border border-slate-200
                            shadow-sm
                            flex items-center justify-center
                            overflow-hidden
                            shrink-0
                        "
                    >
                        <img
                            src="{{ $logo }}"
                            alt="{{ $rekening->bank }}"
                            class="w-8 h-8 object-contain"
                            loading="lazy"
                            onerror="this.onerror=null;this.src='{{ $fallbackLogo }}';"
                        >
                    </div>

                    <div class="flex-1">

                        <div class="text-xs text-slate-500">
                            {{ $rekening->bank }}
                        </div>

                        <div class="text-lg font-black tracking-widest text-slate-900">
                            {{ $rekening->no_rekening }}
                        </div>

                        <div class="text-xs text-slate-500 mt-1">
                            a.n {{ $rekening->atas_nama }}
                        </div>

                    </div>

                </div>

                <button
                    type="button"
                    onclick="copyRekening('{{ $rekening->no_rekening }}', this)"
                    class="shrink-0 flex items-center gap-1 px-3 py-2 rounded-xl bg-[#00A39D]/10 text-[#00A39D] text-xs font-semibold">

                    <x-heroicon-o-clipboard-document class="w-4 h-4" />
                    Copy
                </button>

            </div>

        </div>

    @else

        <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-700">
            Rekening tujuan belum diset untuk tagihan ini.
        </div>

    @endif

</div>

    {{-- FORM TRANSFER --}}
    <div
        class="mt-5 rounded-3xl bg-white border border-slate-200 p-4 shadow-sm">

        <h3 class="font-semibold text-slate-900">

            Konfirmasi Pembayaran

        </h3>

        <form
        action="{{ route('ppdb.pembayaran.transfer.store', $tagihan) }}"
        method="POST"
        enctype="multipart/form-data"
        class="mt-4">

        @csrf

        {{-- NOMINAL --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">
                Nominal Transfer
            </label>

            <input
                type="number"
                name="nominal"
                id="nominal-input"
                min="1000"
                max="{{ $sisaTagihan }}"
                value="{{ old('nominal', $sisaTagihan) }}"
                required
                oninput="document.getElementById('nominal-preview').innerText = 'Rp ' + Number(this.value || 0).toLocaleString('id-ID')"
                class="w-full border border-slate-200 rounded-2xl p-3">

            <p id="nominal-preview" class="text-xs text-slate-500 mt-1">
                Rp {{ number_format(old('nominal', $sisaTagihan), 0, ',', '.') }}
            </p>
        </div>

        {{-- BUKTI TRANSFER --}}
        <div class="mt-4">

            <label class="block text-sm font-medium text-slate-700 mb-2">
                Upload Bukti Transfer
            </label>

            <input
                type="file"
                name="bukti_transfer_ppdb"
                accept="image/*"
                required
                class="
                    w-full
                    border
                    border-slate-200
                    rounded-2xl
                    p-3
                    bg-white
                ">

            @error('bukti_transfer')
                <div class="text-red-500 text-xs mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <button
            type="submit"
            class="
                w-full
                mt-5
                h-12
                rounded-2xl
                bg-[#00A39D]
                text-white
                font-semibold
            ">
            Kirim Bukti Transfer
        </button>

    </form>

    </div>

</div>
<script>

function copyRekening(nomor, btn)
{
    navigator.clipboard.writeText(nomor);

    let oldText = btn.innerHTML;

    btn.innerHTML = '✓ Tersalin';

    setTimeout(() => {

        btn.innerHTML = oldText;

    }, 2000);
}

</script>

@endsection