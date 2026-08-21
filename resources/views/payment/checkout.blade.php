@extends($layout ?? 'wali.layout.wali')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-[#F6F8FB] via-white to-[#EEF7F6]">

    <div class="max-w-lg mx-auto px-4 py-8">

        {{-- BRANDING SEKOLAH/YAYASAN -- bukan Qinara --}}
        <div class="flex flex-col items-center mb-6">

            @if($logo)
                <img src="{{ $logo }}" alt="{{ $namaLembaga }}"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                     class="w-16 h-16 rounded-2xl object-cover shadow-sm border border-slate-200 mb-3">
                <div class="w-16 h-16 rounded-2xl bg-[#00A39D] text-white items-center justify-center text-2xl font-bold shadow-sm mb-3" style="display:none;">
                    {{ strtoupper(substr($namaLembaga, 0, 1)) }}
                </div>
            @else
                <div class="w-16 h-16 rounded-2xl bg-[#00A39D] text-white flex items-center justify-center text-2xl font-bold shadow-sm mb-3">
                    {{ strtoupper(substr($namaLembaga, 0, 1)) }}
                </div>
            @endif

            <div class="text-sm font-semibold text-slate-900">{{ $namaLembaga }}</div>
            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                <x-heroicon-o-lock-closed class="w-3 h-3" />
                Pembayaran Aman
            </div>

        </div>

        {{-- RINGKASAN TAGIHAN --}}
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-5 mb-5">

            <div class="flex justify-between items-start">
                <div>
                    <div class="text-xs text-slate-400">Nomor Invoice</div>
                    <div class="text-sm font-mono font-medium text-slate-700">{{ $referenceId }}</div>
                </div>
                @if(isset($countdownTo))
                    <div class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-full px-3 py-1 font-medium"
                         data-countdown="{{ $countdownTo }}">
                        --:--
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-4 border-t border-dashed border-slate-200">
                <div class="text-xs text-slate-400">{{ $judul }}</div>

                @if(isset($feeAdmin) && $feeAdmin > 0)
                    <div class="flex justify-between items-center text-sm text-slate-500 mt-2">
                        <span>Tagihan</span>
                        <span>Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm text-slate-500 mt-1">
                        <span>Biaya Admin</span>
                        <span>Rp {{ number_format($feeAdmin, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-100">
                        <span class="text-sm font-semibold text-slate-700">Total Bayar</span>
                        <span class="text-2xl font-bold text-slate-900">Rp {{ number_format($amountCharged, 0, ',', '.') }}</span>
                    </div>
                @else
                    <div class="text-2xl font-bold text-slate-900 mt-1">
                        Rp {{ number_format($amount, 0, ',', '.') }}
                    </div>
                @endif
            </div>

        </div>

        {{-- KONTEN PEMBAYARAN: VA ATAU QRIS --}}
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm p-5 mb-5">

            @if($channel === 'VA')

                <div class="text-sm font-semibold text-slate-900 mb-1">Transfer ke Virtual Account</div>
                <p class="text-xs text-slate-500 mb-4">
                    Nomor ini bisa ditransfer dari <strong>bank/e-wallet manapun</strong> (m-banking, ATM, atau internet banking).
                </p>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-3 mb-4">
                    <div class="text-xl font-mono font-bold tracking-widest text-slate-900 break-all">
                        {{ $vaNumber }}
                    </div>
                    <button type="button" onclick="copyToClipboard('{{ $vaNumber }}', this)"
                            class="shrink-0 flex items-center gap-1 px-3 py-2 rounded-xl bg-[#00A39D]/10 text-[#00A39D] text-xs font-semibold">
                        <x-heroicon-o-clipboard-document class="w-4 h-4" />
                        Salin
                    </button>
                </div>

                {{-- Badge informasi -- VA ini 1 nomor, bisa dibayar dari
                     bank manapun. Ini VISUAL saja, bukan pilihan yang
                     mempengaruhi request ke DOKU. --}}
                <div class="mb-5">
                    <div class="text-[11px] text-slate-400 mb-2">Bisa dibayar dari bank manapun, termasuk:</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['BCA', 'BNI', 'BRI', 'Mandiri', 'BSI', 'CIMB', 'Permata', 'Danamon'] as $bankNama)
                            <span class="text-[10px] font-semibold text-slate-500 bg-slate-100 rounded-lg px-2.5 py-1.5">{{ $bankNama }}</span>
                        @endforeach
                        <span class="text-[10px] font-semibold text-slate-400 bg-slate-50 rounded-lg px-2.5 py-1.5">+ lainnya</span>
                    </div>
                </div>

                {{-- Tata cara pembayaran --}}
                <div class="border-t border-dashed border-slate-200 pt-4">
                    <button type="button" onclick="document.getElementById('cara-bayar').classList.toggle('hidden')"
                            class="w-full flex items-center justify-between text-sm font-semibold text-slate-700">
                        <span class="flex items-center gap-2">
                            <x-heroicon-o-information-circle class="w-4 h-4 text-slate-400" />
                            Cara Pembayaran
                        </span>
                        <x-heroicon-o-chevron-down class="w-4 h-4 text-slate-400" />
                    </button>

                    <div id="cara-bayar" class="hidden mt-3 space-y-3">

                        <div class="bg-slate-50 rounded-2xl p-3">
                            <div class="text-xs font-semibold text-slate-700 mb-1.5">Lewat M-Banking</div>
                            <ol class="text-xs text-slate-500 space-y-1 list-decimal list-inside">
                                <li>Buka aplikasi m-banking bank Anda</li>
                                <li>Pilih menu <strong>Transfer</strong> &rarr; <strong>Virtual Account / Ke Bank Lain</strong></li>
                                <li>Masukkan nomor VA di atas, lalu salin nominal tagihan persis</li>
                                <li>Pastikan nama penerima muncul sesuai, lalu konfirmasi</li>
                            </ol>
                        </div>

                        <div class="bg-slate-50 rounded-2xl p-3">
                            <div class="text-xs font-semibold text-slate-700 mb-1.5">Lewat ATM</div>
                            <ol class="text-xs text-slate-500 space-y-1 list-decimal list-inside">
                                <li>Masukkan kartu ATM, pilih <strong>Transfer</strong></li>
                                <li>Pilih <strong>Ke Rekening Bank Lain / Virtual Account</strong></li>
                                <li>Masukkan nomor VA di atas dan nominal tagihan</li>
                                <li>Ikuti instruksi sampai selesai</li>
                            </ol>
                        </div>

                        <p class="text-[11px] text-amber-600 bg-amber-50 rounded-xl px-3 py-2">
                            Pastikan nominal transfer <strong>persis sama</strong> dengan Total Bayar (Rp {{ number_format($amountCharged ?? $amount, 0, ',', '.') }}) supaya pembayaran otomatis terverifikasi.
                        </p>

                    </div>
                </div>

            @elseif($channel === 'QRIS')

                <div class="text-sm font-semibold text-slate-900 mb-1">Scan QRIS untuk Bayar</div>
                <p class="text-xs text-slate-500 mb-4">
                    Buka aplikasi e-wallet atau m-banking apapun yang mendukung QRIS, lalu scan kode di bawah ini.
                </p>

                <div class="flex justify-center">
                    <div class="bg-white border border-slate-200 rounded-2xl p-4">
                        @if(isset($qrImageBase64))
                            <img src="data:image/png;base64,{{ $qrImageBase64 }}" alt="QRIS" class="w-56 h-56 object-contain">
                        @elseif(isset($qrString))
                            <div id="qris-canvas" class="w-56 h-56" data-qr-string="{{ $qrString }}"></div>
                        @endif
                    </div>
                </div>

            @elseif($channel === 'ALFAMART' || $channel === 'INDOMARET')

                <div class="text-sm font-semibold text-slate-900 mb-1">Bayar di {{ ucfirst(strtolower($channel)) }}</div>
                <p class="text-xs text-slate-500 mb-4">
                    Tunjukkan kode ini ke kasir {{ ucfirst(strtolower($channel)) }} terdekat untuk membayar tunai.
                </p>

                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 flex items-center justify-between gap-3">
                    <div class="text-xl font-mono font-bold tracking-widest text-slate-900 break-all">
                        {{ $paymentCode }}
                    </div>
                    <button type="button" onclick="copyToClipboard('{{ $paymentCode }}', this)"
                            class="shrink-0 flex items-center gap-1 px-3 py-2 rounded-xl bg-[#00A39D]/10 text-[#00A39D] text-xs font-semibold">
                        <x-heroicon-o-clipboard-document class="w-4 h-4" />
                        Salin
                    </button>
                </div>

            @endif

        </div>

        {{-- CEK STATUS --}}
        <button
            type="button"
            id="cek-status-btn"
            data-status-url="{{ $statusUrl }}"
            data-redirect-url="{{ $successUrl }}"
            class="w-full h-12 rounded-2xl bg-[#00A39D] hover:bg-[#008f89] text-white text-sm font-semibold transition flex items-center justify-center gap-2">
            <x-heroicon-o-arrow-path class="w-4 h-4" />
            Cek Status Pembayaran
        </button>

        <p class="text-center text-xs text-slate-400 mt-4">
            Diproses oleh DOKU, partner payment gateway resmi {{ $namaLembaga }}
        </p>

    </div>

</div>

@if(isset($qrString) && !isset($qrImageBase64))
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qris-canvas'), {
        text: document.getElementById('qris-canvas').dataset.qrString,
        width: 224,
        height: 224,
    });
</script>
@endif

<script>
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text);
    const old = btn.innerHTML;
    btn.innerHTML = '&#10003; Tersalin';
    setTimeout(() => { btn.innerHTML = old; }, 2000);
}

document.getElementById('cek-status-btn')?.addEventListener('click', async function () {
    const btn = this;
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Mengecek...';

    try {
        const res = await fetch(btn.dataset.statusUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (data.status === 'sukses' || data.status === 'success') {
            window.location.href = btn.dataset.redirectUrl;
        } else {
            btn.innerHTML = 'Belum Dibayar -- Cek Lagi';
            setTimeout(() => { btn.innerHTML = originalHtml; btn.disabled = false; }, 1500);
        }
    } catch (e) {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    }
});

@if(isset($countdownTo))
(function () {
    const el = document.querySelector('[data-countdown]');
    if (!el) return;
    const target = new Date(el.dataset.countdown).getTime();

    const tick = () => {
        const diff = target - Date.now();
        if (diff <= 0) { el.textContent = 'Kedaluwarsa'; return; }
        const m = Math.floor(diff / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    };
    tick();
    setInterval(tick, 1000);
})();
@endif
</script>

@endsection
