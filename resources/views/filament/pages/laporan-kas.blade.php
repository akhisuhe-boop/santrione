<x-filament::page>

    @php
        $summary = $this->getSummary();
    @endphp

    {{-- FILTER --}}
    <div class="mb-6">
        {{ $this->form }}
    </div>

    {{-- SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        {{-- MASUK --}}
        <div style="background:#ecfdf5; border-radius:16px; border:1px solid #bbf7d0;" class="p-4 shadow-sm">
            <div style="color:#166534;" class="text-sm">Total Kas Masuk</div>
            <div style="color:#15803d;" class="text-2xl font-bold">
                Rp {{ number_format($summary['masuk'], 0, ',', '.') }}
            </div>
        </div>

        {{-- KELUAR --}}
        <div style="background:#fef2f2; border-radius:16px; border:1px solid #fecaca;" class="p-4 shadow-sm">
            <div style="color:#991b1b;" class="text-sm">Total Kas Keluar</div>
            <div style="color:#b91c1c;" class="text-2xl font-bold">
                Rp {{ number_format($summary['keluar'], 0, ',', '.') }}
            </div>
        </div>

        {{-- SALDO --}}
        <div style="background:#eff6ff; border-radius:16px; border:1px solid #bfdbfe;" class="p-4 shadow-sm">
            <div style="color:#1e40af;" class="text-sm">Saldo Akhir</div>
            <div style="color:#1d4ed8;" class="text-2xl font-bold">
                Rp {{ number_format($summary['saldo'], 0, ',', '.') }}
            </div>
        </div>

    </div>

    {{-- TABLE --}}
    {{ $this->table }}

</x-filament::page>