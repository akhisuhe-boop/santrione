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
        $tahunanDipilih = $this->isTahunanDipilih();
    @endphp

    {{-- TOGGLE SIKLUS BILLING --}}
    @php
        // Badge "Hemat X%" ambil dari plan yang sedang aktif kalau ada;
        // kalau tenant baru belum punya langganan aktif sama sekali,
        // fallback ke diskon paket "Akses Platform" (basis default)
        // supaya badge-nya tetap muncul, tidak kosong begitu saja.
        $diskonUntukBadge = (int) (
            $subAktif?->plan?->diskon_tahunan_persen
            ?? \App\Models\SubscriptionPlan::where('slug', 'akses-platform')->value('diskon_tahunan_persen')
            ?? 0
        );
    @endphp
    <div class="flex flex-col items-center gap-4 mb-2">
        <div class="inline-flex items-center bg-gray-100 dark:bg-gray-800 rounded-full p-1">
            <button
                type="button"
                wire:click="setBillingCycle('bulanan')"
                class="px-6 py-2.5 rounded-full text-sm font-bold transition-all {{ ! $tahunanDipilih ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500' }}"
            >
                Bulanan
            </button>
            <button
                type="button"
                wire:click="setBillingCycle('tahunan')"
                class="px-6 py-2.5 rounded-full text-sm font-bold transition-all flex items-center gap-2 {{ $tahunanDipilih ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500' }}"
            >
                Tahunan
                @if ($diskonUntukBadge > 0)
                    <span class="text-[10px] font-bold bg-success-100 text-success-700 px-2 py-0.5 rounded-full">
                        Hemat {{ $diskonUntukBadge }}%
                    </span>
                @endif
            </button>
        </div>
        @if ($tahunanDipilih)
            <p class="text-xs text-gray-400">Bayar 1 tahun sekaligus, langsung dapat diskon. Berlaku untuk pembayaran/aktivasi berikutnya.</p>
        @endif

        @if ($this->isPreviewBerbedaDariAktif())
            <div class="max-w-xl mx-auto text-xs bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 rounded-lg px-4 py-2.5 flex items-start gap-2">
                <x-heroicon-o-information-circle class="w-4 h-4 shrink-0 mt-0.5" />
                <span>
                    Langganan aktif Anda: <strong>{{ $subAktif->isTahunan() ? 'Tahunan' : 'Bulanan' }}</strong> sampai <strong>{{ $subAktif->berakhir_pada?->locale('id')->translatedFormat('d M Y') }}</strong>.
                    Tampilan di atas cuma pratinjau — belum ada tagihan sekarang.
                </span>
            </div>
        @endif
    </div>

    {{-- TAGIHAN --}}
    <x-filament::section :heading="$tahunanDipilih ? 'Tagihan Tahunan' : 'Tagihan Bulanan'" icon="heroicon-o-document-text">

        <div class="flex items-center justify-between flex-wrap gap-3 mb-4 text-sm">
            <div class="text-gray-500">
                {{ $estimasi['total_siswa'] }} siswa · {{ count($estimasi['lembaga']) }} lembaga
            </div>

            <div class="flex items-center gap-3">
                @if ($paketFullAktif)
                    <x-filament::badge color="warning" icon="heroicon-o-sparkles">Paket Full Aktif</x-filament::badge>
                @endif

                @if ($subAktif)
                    <x-filament::badge :color="$subAktif->isTahunan() ? 'success' : 'gray'">
                        Langganan Aktif: {{ $subAktif->isTahunan() ? 'Tahunan' : 'Bulanan' }}
                    </x-filament::badge>
                @endif

                <div class="text-gray-500">
                    Jatuh Tempo:
                    <span class="font-semibold text-gray-800 dark:text-gray-100">
                        {{ $subAktif?->berakhir_pada?->locale('id')->translatedFormat('d M Y') ?? 'Belum ada langganan aktif' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-gray-400 border-b border-gray-100 dark:border-gray-700">
                        <th class="px-2 py-2 font-semibold">Lembaga</th>
                        <th class="px-2 py-2 font-semibold">Siswa</th>
                        <th class="px-2 py-2 font-semibold">Akses Platform</th>
                        <th class="px-2 py-2 font-semibold">Diskon Volume</th>
                        <th class="px-2 py-2 font-semibold">Modul</th>
                        <th class="px-2 py-2 font-semibold text-right">Subtotal / bulan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estimasi['lembaga'] as $l)
                        @php
                            $modulAktifList = collect($l['modul'])->filter(fn ($m) => $m['harga'] > 0 || ($m['termasuk_paket_full'] ?? false));
                            $modulLabel = $modulAktifList->isNotEmpty()
                                ? $modulAktifList->pluck('nama')->implode(' + ') . ' = Rp ' . number_format($l['total_modul'], 0, ',', '.')
                                : '—';
                        @endphp
                        <tr class="border-b border-gray-50 dark:border-gray-800 align-top">
                            <td class="px-2 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $l['lembaga_nama'] }}<br><span class="text-xs text-gray-400 font-normal">(Lembaga ke-{{ $l['urutan_ke'] }})</span></td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $l['jumlah_siswa'] }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">Rp {{ number_format($l['akses_platform_sebelum_diskon'], 0, ',', '.') }}</td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">
                                @if ($l['diskon_persen'] > 0)
                                    <span class="text-success-600 font-medium">{{ $l['diskon_persen'] }}%</span> &rarr; Rp {{ number_format($l['akses_platform'], 0, ',', '.') }}
                                @else
                                    0%
                                @endif
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">{{ $modulLabel }}</td>
                            <td class="px-2 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($l['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    @if ($tahunanDipilih)
                        <tr class="text-gray-500">
                            <td colspan="5" class="px-2 py-2">Total {{ count($estimasi['lembaga']) }} lembaga × 12 bulan</td>
                            <td class="px-2 py-2 text-right">Rp {{ number_format($estimasi['total_tahunan_sebelum_diskon'], 0, ',', '.') }}</td>
                        </tr>
                        @if ($estimasi['diskon_tahunan_persen'] > 0)
                        <tr class="text-success-600">
                            <td colspan="5" class="px-2 py-2 font-medium">Diskon Tahunan ({{ $estimasi['diskon_tahunan_persen'] }}%)</td>
                            <td class="px-2 py-2 text-right font-medium">- Rp {{ number_format($estimasi['total_tahunan_sebelum_diskon'] - $estimasi['total'], 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="5" class="px-2 py-3 font-bold text-gray-900 dark:text-white">TOTAL DIBAYAR (1 TAHUN)</td>
                            <td class="px-2 py-3 text-right font-bold text-primary-600">Rp {{ number_format($estimasi['total'], 0, ',', '.') }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="5" class="px-2 py-3 font-bold text-gray-900 dark:text-white">TOTAL YAYASAN / BULAN</td>
                            <td class="px-2 py-3 text-right font-bold text-primary-600">Rp {{ number_format($estimasi['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </x-filament::section>

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
                    <div class="text-sm text-gray-500">Aktifkan sekarang lewat Xendit (QRIS / VA / E-Wallet) — siklus {{ $tahunanDipilih ? 'tahunan' : 'bulanan' }} sesuai pilihan di atas.</div>
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
                    <div class="font-medium text-gray-800 dark:text-gray-100">
                        {{ $sub->plan->nama ?? '—' }}
                        <span class="text-xs font-normal text-gray-400">({{ $sub->isTahunan() ? 'Tahunan' : 'Bulanan' }})</span>
                    </div>
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
