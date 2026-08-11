<x-filament-panels::page>

    @php
        $estimasi = $this->getEstimasi();
        $lembagas = $this->getLembagas();
        $modulOptions = $this->getModulOptions();
        $subAktif = $this->getSubscriptionAktif();
        $pendingUrl = $this->getPendingPaymentUrl();
        $broadcasts = $this->getBroadcasts();
        $riwayat = $this->getRiwayatPembayaran();
    @endphp

    {{-- RINGKASAN --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <x-filament::section>
            <div class="text-sm text-gray-500 mb-1">Estimasi Tagihan Bulan Ini</div>
            <div class="text-2xl font-bold text-primary-600">Rp {{ number_format($estimasi['total'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $estimasi['total_siswa'] }} siswa · {{ count($estimasi['lembaga']) }} lembaga</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 mb-1">Jatuh Tempo Berikutnya</div>
            @if ($subAktif?->berakhir_pada)
                <div class="text-2xl font-bold text-gray-800">{{ $subAktif->berakhir_pada->locale('id')->translatedFormat('d M Y') }}</div>
            @else
                <div class="text-lg font-semibold text-gray-400">Belum ada langganan aktif</div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500 mb-1">Total Modul Aktif</div>
            <div class="text-2xl font-bold text-gray-800">{{ collect($estimasi['lembaga'])->sum(fn ($l) => count($l['modul'])) }}</div>
            <div class="text-xs text-gray-400 mt-1">di seluruh lembaga</div>
        </x-filament::section>

    </div>

    {{-- TAGIHAN PENDING --}}
    @if ($pendingUrl)
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="font-semibold text-gray-900">Ada tagihan menunggu pembayaran</div>
                    <div class="text-sm text-gray-500">Selesaikan pembayaran supaya langganan tetap aktif.</div>
                </div>
                <a href="{{ $pendingUrl }}" target="_blank">
                    <x-filament::button color="warning">Bayar Sekarang</x-filament::button>
                </a>
            </div>
        </x-filament::section>
    @elseif (! $subAktif || $subAktif->status !== 'active')
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="font-semibold text-gray-900">Belum ada langganan aktif</div>
                    <div class="text-sm text-gray-500">Aktifkan sekarang lewat Xendit (QRIS / VA / E-Wallet).</div>
                </div>
                <x-filament::button wire:click="bayarSekarang" color="primary">
                    Bayar Sekarang
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif

    {{-- PILIH MODUL PER LEMBAGA --}}
    @foreach ($lembagas as $lembaga)
        <x-filament::section :heading="'Modul — ' . $lembaga->nama">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($modulOptions as $modul)
                    @php $aktif = $this->isModuleActive($lembaga->id, $modul->id); @endphp

                    <label class="flex items-center justify-between gap-3 rounded-xl border {{ $aktif ? 'border-primary-400 bg-primary-50' : 'border-gray-200' }} px-4 py-3 text-sm cursor-pointer">
                        <span class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                wire:click="toggleModule({{ $lembaga->id }}, {{ $modul->id }})"
                                {{ $aktif ? 'checked' : '' }}
                                class="rounded text-primary-600 focus:ring-primary-500"
                            >
                            <span class="font-medium text-gray-700">{{ $modul->nama }}</span>
                        </span>
                        <span class="text-gray-500 text-xs">
                            {{ $modul->is_gratis ? 'Gratis (dari wali murid)' : 'Rp ' . number_format($modul->harga_bulanan, 0, ',', '.') . '/bln' }}
                        </span>
                    </label>
                @endforeach
            </div>

        </x-filament::section>
    @endforeach

    {{-- INFO DARI QINARA --}}
    @if ($broadcasts->isNotEmpty())
        <x-filament::section heading="Info dari Qinara">
            <div class="space-y-4">
                @foreach ($broadcasts as $b)
                    <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                        <div class="font-semibold text-gray-800">{{ $b->judul }}</div>
                        <p class="text-sm text-gray-600 mt-1 whitespace-pre-line">{{ $b->pesan }}</p>
                        <div class="text-xs text-gray-400 mt-2">{{ $b->dikirim_pada?->locale('id')->translatedFormat('d M Y') }}</div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    {{-- RIWAYAT PEMBAYARAN --}}
    <x-filament::section heading="Riwayat Langganan">
        @forelse ($riwayat as $sub)
            <div class="flex items-center justify-between text-sm border-b border-gray-100 py-3 last:border-0">
                <div>
                    <div class="font-medium text-gray-800">{{ $sub->plan->nama ?? '—' }}</div>
                    <div class="text-gray-400 text-xs">{{ $sub->created_at->locale('id')->translatedFormat('d M Y H:i') }}</div>
                </div>
                <x-filament::badge :color="match($sub->status) { 'active' => 'success', 'pending' => 'warning', default => 'gray' }">
                    {{ ucfirst($sub->status) }}
                </x-filament::badge>
            </div>
        @empty
            <p class="text-sm text-gray-400">Belum ada riwayat.</p>
        @endforelse
    </x-filament::section>

</x-filament-panels::page>
