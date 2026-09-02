<x-filament::page>

    @php
        $summary = $this->getSummary();
    @endphp

    {{-- FILTER --}}
    <div class="mb-6">
        {{ $this->form }}
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">

        <div style="background:#eff6ff; border-radius:16px; border:1px solid #bfdbfe;" class="p-4 shadow-sm">
            <div style="color:#1e40af;" class="text-sm">Total Transaksi</div>
            <div style="color:#1d4ed8;" class="text-2xl font-bold">
                {{ number_format($summary['total_transaksi'], 0, ',', '.') }}
            </div>
            <div style="color:#3b82f6;" class="text-xs mt-1">
                Omzet Rp {{ number_format($summary['total_omzet'], 0, ',', '.') }}
            </div>
        </div>

        <div style="background:#ecfdf5; border-radius:16px; border:1px solid #bbf7d0;" class="p-4 shadow-sm">
            <div style="color:#166534;" class="text-sm">Wallet</div>
            <div style="color:#15803d;" class="text-2xl font-bold">
                Rp {{ number_format($summary['wallet_total'], 0, ',', '.') }}
            </div>
            <div style="color:#16a34a;" class="text-xs mt-1">
                {{ number_format($summary['wallet_count'], 0, ',', '.') }} transaksi
            </div>
        </div>

        <div style="background:#fff7ed; border-radius:16px; border:1px solid #fed7aa;" class="p-4 shadow-sm">
            <div style="color:#9a3412;" class="text-sm">
                Tunai
                @if ($summary['rasio_tunai_persen'] >= 30)
                    <span style="background:#fecaca;color:#991b1b;" class="ml-1 text-[10px] font-semibold px-2 py-0.5 rounded-full">rasio tinggi</span>
                @endif
            </div>
            <div style="color:#c2410c;" class="text-2xl font-bold">
                Rp {{ number_format($summary['tunai_total'], 0, ',', '.') }}
            </div>
            <div style="color:#ea580c;" class="text-xs mt-1">
                {{ number_format($summary['tunai_count'], 0, ',', '.') }} transaksi &middot; {{ $summary['rasio_tunai_persen'] }}% dari total transaksi
            </div>
        </div>

    </div>

    {{-- TABLE --}}
    {{ $this->table }}

</x-filament::page>
