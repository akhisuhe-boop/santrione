<x-filament-panels::page>

    @php
        $estimasi = $this->getEstimasi();
        $lembagas = $this->getLembagas();
        $modulOptions = $this->getModulOptions();
        $subAktif = $this->getSubscriptionAktif();
        $pendingUrl = $this->getPendingPaymentUrl();
        $broadcasts = $this->getBroadcasts();
        $riwayat = $this->getRiwayatPembayaran();
        $paketFullAktif = $this->isPaketFullAktif();
    @endphp

    {{-- HERO: TOTAL TAGIHAN --}}
    <div
        class="rounded-2xl p-8 text-white shadow-lg"
        style="background: linear-gradient(135deg, #0f9c94 0%, #0b7a73 100%);"
    >
        <div class="flex items-start justify-between flex-wrap gap-6">
            <div>
                <div class="text-sm font-medium uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Estimasi Tagihan Bulan Ini</div>
                <div class="text-4xl font-extrabold mt-1" style="color: #ffffff;">Rp {{ number_format($estimasi['total'], 0, ',', '.') }}</div>
                <div class="text-sm mt-2" style="color: rgba(255,255,255,0.8);">{{ $estimasi['total_siswa'] }} siswa · {{ count($estimasi['lembaga']) }} lembaga</div>
            </div>

            <div class="text-right">
                <div class="text-sm font-medium uppercase tracking-wide" style="color: rgba(255,255,255,0.8);">Jatuh Tempo</div>
                @if ($subAktif?->berakhir_pada)
                    <div class="text-xl font-bold mt-1" style="color: #ffffff;">{{ $subAktif->berakhir_pada->locale('id')->translatedFormat('d M Y') }}</div>
                @else
                    <div class="text-lg font-semibold mt-1" style="color: rgba(255,255,255,0.8);">Belum ada langganan aktif</div>
                @endif

                @if ($paketFullAktif)
                    <span class="inline-flex items-center gap-1 mt-2 rounded-full px-3 py-1 text-xs font-semibold" style="background: rgba(255,255,255,0.2); color: #ffffff;">
                        ⭐ Paket Full Aktif
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- TAGIHAN PENDING / BELUM ADA LANGGANAN --}}
    @if ($pendingUrl)
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white">Ada tagihan menunggu pembayaran</div>
                    <div class="text-sm text-gray-500">Selesaikan pembayaran supaya langganan tetap aktif.</div>
                </div>
                <a href="{{ $pendingUrl }}" target="_blank">
                    <x-filament::button color="warning" icon="heroicon-o-credit-card">Bayar Sekarang</x-filament::button>
                </a>
            </div>
        </x-filament::section>
    @elseif (! $subAktif || $subAktif->status !== 'active')
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white">Belum ada langganan aktif</div>
                    <div class="text-sm text-gray-500">Aktifkan sekarang lewat Xendit (QRIS / VA / E-Wallet).</div>
                </div>
                <x-filament::button wire:click="bayarSekarang" color="primary" icon="heroicon-o-credit-card">
                    Bayar Sekarang
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif

    {{-- PAKET FULL SHORTCUT --}}
    @if (! $paketFullAktif)
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-warning-100 dark:bg-warning-500/10 p-3">
                        <x-heroicon-o-sparkles class="w-6 h-6 text-warning-600" />
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">Mau semua modul sekaligus?</div>
                        <div class="text-sm text-gray-500">Aktifkan Paket Full — lebih hemat daripada pilih modul satu-satu.</div>
                    </div>
                </div>
                <x-filament::button wire:click="aktifkanPaketFull" color="warning" outlined>
                    Aktifkan Paket Full
                </x-filament::button>
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-success-100 dark:bg-success-500/10 p-3">
                        <x-heroicon-o-check-badge class="w-6 h-6 text-success-600" />
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">Paket Full sedang aktif</div>
                        <div class="text-sm text-gray-500">Semua modul di bawah termasuk otomatis, tidak dihitung terpisah.</div>
                    </div>
                </div>
                <x-filament::button wire:click="batalkanPaketFull" color="gray" outlined>
                    Kembali Pilih Satu-satu
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif

    {{-- BELUM ADA LEMBAGA --}}
    @if ($lembagas->isEmpty())
        <x-filament::section>
            <div class="text-center py-8">
                <x-heroicon-o-building-office-2 class="w-10 h-10 mx-auto text-gray-300" />
                <div class="font-semibold text-gray-900 dark:text-white mt-3">Belum ada Lembaga</div>
                <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                    Buat Lembaga (unit sekolah) dulu di menu <strong>Master Data &rarr; Lembaga</strong>, baru Anda bisa pilih modul yang mau diaktifkan di sini.
                </p>
                <a href="{{ \App\Filament\Resources\LembagaResource::getUrl('create', tenant: $this->getYayasan()) }}" class="inline-block mt-4">
                    <x-filament::button icon="heroicon-o-plus">Buat Lembaga</x-filament::button>
                </a>
            </div>
        </x-filament::section>
    @else

        {{-- PILIH MODUL PER LEMBAGA --}}
        @foreach ($lembagas as $lembaga)
            <x-filament::section :heading="'Modul — ' . $lembaga->nama">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($modulOptions as $modul)
                        @php $aktif = $paketFullAktif ? true : $this->isModuleActive($lembaga->id, $modul->id); @endphp

                        <label class="flex items-center justify-between gap-3 rounded-xl border transition-colors {{ $aktif ? 'border-primary-400 bg-primary-50 dark:bg-primary-500/10' : 'border-gray-200 dark:border-gray-700 hover:border-primary-300' }} px-4 py-3 text-sm {{ $paketFullAktif ? '' : 'cursor-pointer' }}">
                            <span class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    @if (! $paketFullAktif) wire:click="toggleModule({{ $lembaga->id }}, {{ $modul->id }})" @endif
                                    {{ $aktif ? 'checked' : '' }}
                                    {{ $paketFullAktif ? 'disabled' : '' }}
                                    class="rounded text-primary-600 focus:ring-primary-500 disabled:opacity-60"
                                >
                                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $modul->nama }}</span>
                            </span>
                            <span class="text-gray-500 text-xs font-medium">
                                @if ($paketFullAktif && ! $modul->is_gratis)
                                    Termasuk Paket Full
                                @else
                                    {{ $modul->is_gratis ? 'Gratis (dari wali murid)' : 'Rp ' . number_format($modul->harga_bulanan, 0, ',', '.') . '/bln' }}
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>

            </x-filament::section>
        @endforeach

    @endif

    {{-- INFO DARI QINARA --}}
    @if ($broadcasts->isNotEmpty())
        <x-filament::section heading="Info dari Qinara" icon="heroicon-o-megaphone">
            <div class="space-y-4">
                @foreach ($broadcasts as $b)
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-4 last:border-0 last:pb-0">
                        <div class="font-semibold text-gray-800 dark:text-gray-100">{{ $b->judul }}</div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 whitespace-pre-line">{{ $b->pesan }}</p>
                        <div class="text-xs text-gray-400 mt-2">{{ $b->dikirim_pada?->locale('id')->translatedFormat('d M Y') }}</div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    {{-- RIWAYAT PEMBAYARAN --}}
    <x-filament::section heading="Riwayat Langganan" icon="heroicon-o-clock">
        @forelse ($riwayat as $sub)
            <div class="flex items-center justify-between text-sm border-b border-gray-100 dark:border-gray-700 py-3 last:border-0">
                <div>
                    <div class="font-medium text-gray-800 dark:text-gray-100">{{ $sub->plan->nama ?? '—' }}</div>
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
