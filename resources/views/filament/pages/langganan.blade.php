<x-filament-panels::page>

    @php
        $estimasi = $this->getEstimasi();
        $lembagas = $this->getLembagas();
        $modulOptions = $this->getModulOptions();
        $subAktif = $this->getSubscriptionAktif();
        $broadcasts = $this->getBroadcasts();
        $riwayat = $this->getRiwayatPembayaran();
        $paketFullAktif = $this->isPaketFullAktif();
        $tahunanDipilih = $this->isTahunanDipilih();
        $yayasanUntukTrial = $this->getYayasan();
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
    @elseif ($this->shouldShowBayarButton())
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <div class="font-semibold text-gray-900 dark:text-white">Bayar / Perpanjang Langganan</div>
                    <div class="text-sm text-gray-500">Bayar lewat DOKU (VA semua bank / QRIS / E-Wallet / dll) — siklus {{ $tahunanDipilih ? 'tahunan' : 'bulanan' }} sesuai pilihan di atas, sesuai modul yang sedang dicentang.</div>
                </div>
                <x-filament::button wire:click="bayarSekarang" color="primary" icon="heroicon-o-credit-card">
                    Bayar Sekarang
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif

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
                    @if ($this->getYayasan()->status === 'trial')
                        <x-filament::badge color="info">
                            Masa Trial Aktif
                        </x-filament::badge>
                    @else
                        <x-filament::badge :color="$subAktif->isTahunan() ? 'success' : 'gray'">
                            Langganan Aktif: {{ $subAktif->isTahunan() ? 'Tahunan' : 'Bulanan' }}
                        </x-filament::badge>
                    @endif
                @endif

                <div class="text-gray-500">
                    {{ $this->getYayasan()->status === 'trial' ? 'Masa Trial Aktif sampai:' : 'Jatuh Tempo:' }}
                    <span class="font-semibold text-gray-800 dark:text-gray-100">
                        {{ $subAktif?->berakhir_pada?->locale('id')->translatedFormat('d M Y') ?? 'Belum ada langganan aktif' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- RINCIAN PER LEMBAGA (tabel ringkas) --}}
        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-2 py-2 font-semibold">Lembaga</th>
                        <th class="px-2 py-2 font-semibold text-right">Siswa</th>
                        <th class="px-2 py-2 font-semibold text-right">Akses Platform</th>
                        <th class="px-2 py-2 font-semibold">Modul Aktif</th>
                        <th class="px-2 py-2 font-semibold text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estimasi['lembaga'] as $l)
                        @php
                            $modulAktifList = collect($l['modul'])->filter(fn ($m) => $m['harga'] > 0 || ($m['termasuk_paket_full'] ?? false));
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-800 align-top">
                            <td class="px-2 py-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $l['lembaga_nama'] }}</div>
                                <div class="text-xs text-gray-400">Lembaga ke-{{ $l['urutan_ke'] }}</div>
                            </td>
                            <td class="px-2 py-3 text-right text-gray-600 dark:text-gray-300">{{ $l['jumlah_siswa'] }}</td>
                            <td class="px-2 py-3 text-right">
                                <div class="text-gray-900 dark:text-white font-medium">Rp {{ number_format($l['akses_platform'], 0, ',', '.') }}</div>
                                @if ($l['siswa_tambahan'] > 0 || $l['diskon_persen'] > 0)
                                    <div class="text-xs text-gray-400 mt-0.5 whitespace-nowrap">
                                        @if ($l['siswa_tambahan'] > 0)
                                            +{{ $l['siswa_tambahan'] }} siswa
                                        @endif
                                        @if ($l['diskon_persen'] > 0)
                                            <span class="text-success-600">diskon {{ $l['diskon_persen'] }}%</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td class="px-2 py-3 text-gray-600 dark:text-gray-300">
                                @if ($modulAktifList->isEmpty())
                                    <span class="text-gray-400">&mdash;</span>
                                @else
                                    {{ $modulAktifList->pluck('nama')->implode(', ') }}
                                @endif
                            </td>
                            <td class="px-2 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($l['subtotal'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- TOTAL --}}
        <div class="rounded-xl bg-primary-50 dark:bg-primary-500/10 p-4 mt-4">
            @if ($tahunanDipilih)
                <div class="flex justify-between gap-3 text-sm text-gray-500 dark:text-gray-400 mb-1">
                    <span>Total {{ count($estimasi['lembaga']) }} lembaga &times; 12 bulan</span>
                    <span class="shrink-0">Rp {{ number_format($estimasi['total_tahunan_sebelum_diskon'], 0, ',', '.') }}</span>
                </div>
                @if (($estimasi['promo_pendaftaran_persen'] ?? 0) > 0)
                    <div class="flex justify-between gap-3 text-sm text-accent-600 mb-1">
                        <span>
                            Diskon Pendaftaran "{{ $estimasi['promo_pendaftaran_teks'] }}" ({{ $estimasi['promo_pendaftaran_persen'] }}%)
                            <span class="block text-[11px] font-normal text-gray-400">Cuma berlaku 1x untuk tagihan pertama ini saja</span>
                        </span>
                        <span class="shrink-0">&minus; Rp {{ number_format($estimasi['total_tahunan_sebelum_diskon'] - $estimasi['total'], 0, ',', '.') }}</span>
                    </div>
                @elseif ($estimasi['diskon_tahunan_persen'] > 0)
                    <div class="flex justify-between gap-3 text-sm text-success-600 mb-1">
                        <span>Diskon Tahunan ({{ $estimasi['diskon_tahunan_persen'] }}%)</span>
                        <span class="shrink-0">&minus; Rp {{ number_format($estimasi['total_tahunan_sebelum_diskon'] - $estimasi['total'], 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center mt-2 pt-2 border-t border-primary-100 dark:border-primary-500/20">
                    <div>
                        <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Total Yayasan / tahun</p>
                        <p class="text-xs text-primary-500/70 dark:text-primary-400/60">{{ count($estimasi['lembaga']) }} lembaga digabung jadi 1 invoice</p>
                    </div>
                    <p class="text-2xl font-bold text-primary-600">Rp {{ number_format($estimasi['total'], 0, ',', '.') }}</p>
                </div>
            @else
                @if (($estimasi['promo_pendaftaran_persen'] ?? 0) > 0)
                    <div class="flex justify-between gap-3 text-sm text-gray-500 dark:text-gray-400 mb-1">
                        <span>Subtotal Yayasan / bulan</span>
                        <span class="shrink-0">Rp {{ number_format($estimasi['subtotal_sebelum_promo'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-3 text-sm text-accent-600 mb-1">
                        <span>
                            Diskon Pendaftaran "{{ $estimasi['promo_pendaftaran_teks'] }}" ({{ $estimasi['promo_pendaftaran_persen'] }}%)
                            <span class="block text-[11px] font-normal text-gray-400">Cuma berlaku 1x untuk tagihan pertama ini saja</span>
                        </span>
                        <span class="shrink-0">&minus; Rp {{ number_format($estimasi['subtotal_sebelum_promo'] - $estimasi['total'], 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Total Yayasan / bulan</p>
                        <p class="text-xs text-primary-500/70 dark:text-primary-400/60">{{ count($estimasi['lembaga']) }} lembaga digabung jadi 1 invoice</p>
                    </div>
                    <p class="text-2xl font-bold text-primary-600">Rp {{ number_format($estimasi['total'], 0, ',', '.') }}</p>
                </div>
            @endif
        </div>
    </x-filament::section>

    {{-- PAKET FULL SHORTCUT --}}
    @if (! $paketFullAktif)
        @php
            $estimasiPaketFull = $this->getEstimasiPaketFull();
        @endphp
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-4 {{ $estimasiPaketFull ? 'pb-4 border-b border-gray-100 dark:border-gray-700' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-warning-100 dark:bg-warning-500/10 p-3">
                        <x-heroicon-o-sparkles class="w-6 h-6 text-warning-600" />
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">Mau semua modul sekaligus?</div>
                        <div class="text-sm text-gray-500">Aktifkan Paket Full — lebih hemat daripada pilih modul satu-satu.</div>
                    </div>
                </div>
                <x-filament::button wire:click="aktifkanPaketFull" color="warning">
                    Aktifkan Paket Full
                </x-filament::button>
            </div>

            @if ($estimasiPaketFull)
                <div class="pt-4 text-base flex items-center gap-2.5 flex-wrap">
                    <span class="text-gray-500">Sekarang:</span>
                    <span class="font-semibold text-gray-500 line-through">Rp {{ number_format($estimasi['total'], 0, ',', '.') }}</span>
                    <x-heroicon-o-arrow-right class="w-4 h-4 text-warning-500" />
                    <span class="text-gray-700 dark:text-gray-200">Paket Full:</span>
                    <span class="font-extrabold text-lg text-warning-600">Rp {{ number_format($estimasiPaketFull['total'], 0, ',', '.') }}</span>
                    <span class="text-gray-500">/ {{ $tahunanDipilih ? 'tahun' : 'bulan' }}</span>
                </div>
            @endif
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

        {{-- PILIH MODUL (matrix: modul x lembaga, supaya tidak memanjang) --}}
        <x-filament::section heading="Pilih Modul per Lembaga">
            <div class="overflow-x-auto -mx-2">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-2 py-2 font-semibold sticky left-0 bg-white dark:bg-gray-900">Modul</th>
                            <th class="px-2 py-2 font-semibold whitespace-nowrap">Harga</th>
                            @foreach ($lembagas as $lembaga)
                                <th class="px-2 py-2 font-semibold text-center whitespace-nowrap">{{ $lembaga->nama }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modulOptions as $modul)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-2 py-2.5 font-medium text-gray-700 dark:text-gray-200 sticky left-0 bg-white dark:bg-gray-900">
                                    {{ $modul->nama }}
                                </td>
                                <td class="px-2 py-2.5 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $modul->is_gratis ? 'Gratis (wali murid)' : 'Rp ' . number_format($modul->harga_bulanan, 0, ',', '.') . '/bln' }}
                                </td>
                                @foreach ($lembagas as $lembaga)
                                    @php $aktif = $paketFullAktif ? true : $this->isModuleActive($lembaga->id, $modul->id); @endphp
                                    <td class="px-2 py-2.5 text-center">
                                        <input
                                            type="checkbox"
                                            wire:key="modul-check-{{ $lembaga->id }}-{{ $modul->id }}-{{ $aktif ? 'on' : 'off' }}"
                                            @if (! $paketFullAktif) wire:click="toggleModule({{ $lembaga->id }}, {{ $modul->id }})" @endif
                                            {{ $aktif ? 'checked' : '' }}
                                            {{ $paketFullAktif ? 'disabled' : '' }}
                                            class="rounded text-primary-600 focus:ring-primary-500 disabled:opacity-60 {{ $paketFullAktif ? '' : 'cursor-pointer' }}"
                                        >
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($paketFullAktif)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-3 flex items-center gap-1">
                    <x-heroicon-o-check class="w-3.5 h-3.5" />
                    Paket Full aktif — semua modul otomatis termasuk, tidak dihitung terpisah.
                </p>
            @endif
        </x-filament::section>

    @endif

    {{-- INFO DARI QINARA + RIWAYAT LANGGANAN (2 card sejajar) --}}
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
                    // Kolom status di baris Subscription TIDAK otomatis berubah
                    // saat masa berlakunya lewat (lihat CheckExpiredSubscriptions
                    // -- itu cuma nyuspend Yayasan, tidak pernah nurunin status
                    // baris Subscription lama). Jadi beberapa baris lama bisa
                    // sama-sama tercatat 'active' di database walau cuma SATU
                    // yang beneran berlaku (yang match $subAktif, dihitung by
                    // tanggal lewat activeSubscription()). Di sini kita
                    // cocokkan ID-nya supaya cuma yang beneran aktif yang
                    // ditandai hijau -- baris 'active' lain ditampilkan
                    // "Berakhir", bukan "Active" yang menyesatkan.
                    [$labelStatus, $warnaStatus] = match (true) {
                        $sub->status === 'active' && $subAktif && $sub->id === $subAktif->id => ['Aktif', 'success'],
                        $sub->status === 'active' => ['Berakhir', 'gray'],
                        $sub->status === 'pending' => ['Menunggu Pembayaran', 'warning'],
                        default => [ucfirst($sub->status), 'gray'],
                    };

                    // Baris trial-placeholder yang otomatis dibuat waktu daftar
                    // (lihat PublicRegistrationController) -- dikenali dari
                    // berakhir_pada-nya yang PERSIS sama dengan trial_ends_at
                    // milik Yayasan. Ditampilkan "Trial Awal (14 Hari)", BUKAN
                    // nama plan/siklus, supaya tidak dikira langganan
                    // berbayar sungguhan.
                    $iniBarisTrial = $sub->berakhir_pada
                        && $yayasanUntukTrial->trial_ends_at
                        && $sub->berakhir_pada->equalTo($yayasanUntukTrial->trial_ends_at)
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
