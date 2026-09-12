<x-filament-panels::page>

    @php
        $subAktif = $this->getSubscriptionAktif();
        $lembagas = $this->getLembagas();
        $paketFullAktif = $this->isPaketFullAktif();
        $riwayat = $this->getRiwayatPembayaran();
        $broadcasts = $this->getBroadcasts();
        $yayasan = $this->getYayasan();
        $adaManualMenunggu = $this->adaTransferManualMenunggu();
        $checkoutUrl = \App\Filament\Pages\Checkout::getUrl(tenant: $yayasan);
    @endphp

    {{-- STATUS / AKSI BAYAR --}}
    @if ($pendingUrl)
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white">Ada tagihan menunggu pembayaran</div>
                    <div class="text-sm text-gray-500">Selesaikan pembayaran DOKU yang sudah dibuat supaya langganan tetap aktif.</div>
                </div>
                <a href="{{ $pendingUrl }}" target="_blank">
                    <x-filament::button color="warning" icon="heroicon-o-credit-card">Lanjutkan Pembayaran</x-filament::button>
                </a>
            </div>
        </x-filament::section>
    @elseif ($adaManualMenunggu)
        <x-filament::section>
            <div class="flex items-center gap-3">
                <x-heroicon-o-clock class="w-6 h-6 text-warning-500 shrink-0" />
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white">Transfer manual sedang diverifikasi</div>
                    <div class="text-sm text-gray-500">Bukti transfer Anda sudah kami terima, menunggu diverifikasi admin Qinara (biasanya 1x24 jam kerja).</div>
                </div>
            </div>
        </x-filament::section>
    @elseif ($this->shouldShowBayarButton())
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white">Bayar / Kelola Langganan</div>
                    <div class="text-sm text-gray-500">Pilih siklus & modul, lalu bayar lewat DOKU (VA/QRIS/E-Wallet) atau Transfer Manual.</div>
                </div>
                <a href="{{ $checkoutUrl }}">
                    <x-filament::button color="primary" icon="heroicon-o-credit-card">
                        Bayar / Kelola Langganan
                    </x-filament::button>
                </a>
            </div>
        </x-filament::section>
    @endif

    {{-- RINGKASAN LANGGANAN SAAT INI --}}
    <x-filament::section heading="Langganan Saat Ini" icon="heroicon-o-identification">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    @if ($paketFullAktif)
                        <x-filament::badge color="warning" icon="heroicon-o-sparkles">Paket Full</x-filament::badge>
                    @endif

                    @if ($yayasan->status === 'trial')
                        <x-filament::badge color="info">Masa Trial</x-filament::badge>
                    @elseif ($subAktif)
                        <x-filament::badge :color="$subAktif->isTahunan() ? 'success' : 'gray'">
                            Langganan Aktif: {{ $subAktif->isTahunan() ? 'Tahunan' : 'Bulanan' }}
                        </x-filament::badge>
                    @else
                        <x-filament::badge color="danger">Belum Ada Langganan Aktif</x-filament::badge>
                    @endif
                </div>
                <div class="text-sm text-gray-500">
                    {{ $subAktif?->plan?->nama ?? '—' }}
                </div>
            </div>

            <div class="text-sm text-gray-500 text-right">
                {{ $yayasan->status === 'trial' ? 'Masa Trial Aktif sampai:' : 'Jatuh Tempo:' }}
                <div class="font-semibold text-gray-800 dark:text-gray-100 text-base">
                    {{ $subAktif?->berakhir_pada?->locale('id')->translatedFormat('d M Y') ?? '—' }}
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- MODUL AKTIF PER LEMBAGA (READ-ONLY) --}}
    <x-filament::section heading="Modul Aktif per Lembaga" icon="heroicon-o-squares-2x2">
        @if ($lembagas->isEmpty())
            <div class="text-center py-6 text-gray-400">Belum ada Lembaga.</div>
        @else
            <div class="space-y-4">
                @foreach ($lembagas as $lembaga)
                    @php
                        $modulAktif = $lembaga->modules->where('is_active', true);
                    @endphp
                    <div class="border-b border-gray-100 dark:border-gray-800 pb-3 last:border-0 last:pb-0">
                        <div class="font-medium text-gray-900 dark:text-white mb-1">{{ $lembaga->nama }}</div>
                        @if ($paketFullAktif)
                            <div class="text-sm text-success-600 flex items-center gap-1">
                                <x-heroicon-o-check-badge class="w-4 h-4" /> Semua modul aktif (Paket Full)
                            </div>
                        @elseif ($modulAktif->isEmpty())
                            <div class="text-sm text-gray-400">Belum ada modul aktif.</div>
                        @else
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($modulAktif as $lm)
                                    <x-filament::badge color="gray">{{ $lm->modulePrice?->nama }}</x-filament::badge>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>

    {{-- INFO DARI QINARA + RIWAYAT LANGGANAN --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">

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

        <x-filament::section heading="Riwayat Langganan" icon="heroicon-o-clock">
            @forelse ($riwayat as $sub)
                @php
                    [$labelStatus, $warnaStatus] = match (true) {
                        $sub->status === 'active' && $subAktif && $sub->id === $subAktif->id => ['Aktif', 'success'],
                        $sub->status === 'active' => ['Berakhir', 'gray'],
                        $sub->status === 'pending' => ['Menunggu Pembayaran', 'warning'],
                        default => [ucfirst($sub->status), 'gray'],
                    };

                    $iniBarisTrial = $sub->berakhir_pada
                        && $yayasan->trial_ends_at
                        && $sub->berakhir_pada->equalTo($yayasan->trial_ends_at)
                        && ! $sub->payments()->where('status', 'berhasil')->exists();
                @endphp
                <div class="flex items-center justify-between text-sm border-b border-gray-100 dark:border-gray-700 py-3 last:border-0">
                    <div>
                        <div class="font-medium text-gray-800 dark:text-gray-100">
                            @if ($iniBarisTrial)
                                Trial Awal (14 Hari)
                            @else
                                {{ $sub->plan->nama ?? '—' }}
                                <span class="text-xs font-normal text-gray-400">({{ $sub->isTahunan() ? 'Tahunan' : 'Bulanan' }})</span>
                            @endif
                        </div>
                        <div class="text-gray-400 text-xs">{{ $sub->created_at->locale('id')->translatedFormat('d M Y H:i') }}</div>
                    </div>
                    <x-filament::badge :color="$warnaStatus">
                        {{ $labelStatus }}
                    </x-filament::badge>
                </div>
            @empty
                <p class="text-sm text-gray-400">Belum ada riwayat.</p>
            @endforelse
        </x-filament::section>

    </div>

</x-filament-panels::page>
