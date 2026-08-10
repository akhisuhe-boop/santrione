<div class="space-y-4">
    @if ($url)
        <p class="text-sm text-gray-500">
            Salin link ini dan kirimkan manual ke Yayasan (WhatsApp/email) — pengiriman otomatis belum tersedia.
        </p>

        <div class="flex items-center gap-2">
            <input
                type="text"
                readonly
                value="{{ $url }}"
                x-ref="linkInput"
                class="flex-1 rounded-lg border-gray-300 text-sm"
                onclick="this.select()"
            >
            <button
                type="button"
                x-on:click="
                    navigator.clipboard.writeText($refs.linkInput.value);
                    $tooltip('Link disalin!', { timeout: 1500 });
                "
                class="fi-btn fi-btn-size-sm inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white"
            >
                Salin
            </button>
        </div>
    @else
        <p class="text-sm text-gray-400">
            Tidak ada link pembayaran pending untuk Yayasan ini saat ini.
        </p>
    @endif
</div>
