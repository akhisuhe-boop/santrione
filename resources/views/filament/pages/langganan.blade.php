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

        {{-- RINCIAN PER LEMBAGA (kartu 3-kolom, bukan tabel — supaya baris
             yang tidak relevan buat lembaga tsb bisa disembunyikan alih-alih
             dipaksa masuk kolom yang sama buat semua lembaga) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($estimasi['lembaga'] as $l)
                @php
                    $modulAktifList = collect($l['modul'])->filter(fn ($m) => $m['harga'] > 0 || ($m['termasuk_paket_full'] ?? false));
                    $semuaModulTermasukPaketFull = $modulAktifList->isNotEmpty()
                        && $modulAktifList->every(fn ($m) => ($m['termasuk_paket_full'] ?? false) || $m['harga'] === 0);
                    // Kartu sekarang lebih sempit (3 per baris) -- chip modul
                    // dipotong biar kartu nggak jadi tinggi banget kalau
                    // modulnya banyak. Daftar lengkapnya tetap ada, cuma di
                    // "sembunyikan" jadi angka "+N lainnya".
                    $modulTampil = $modulAktifList->take(3);
                    $modulSisa = $modulAktifList->count() - $modulTampil->count();
                @endphp
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-3.5 flex flex-col">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $l['lembaga_nama'] }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                Lembaga ke-{{ $l['urutan_ke'] }}
                            </span>
                        </div>
                        <span class="text-sm text-gray-500 dark:text-gray-400 shrink-0">{{ $l['jumlah_siswa'] }} siswa</span>
                    </div>

                    <div class="text-sm text-gray-500 dark:text-gray-400 space-y-1">
                        <div class="flex justify-between gap-3">
                            <span>{{ $l['lembaga_di_dalam_kuota'] ? 'Akses Platform dasar' : 'Biaya lembaga tambahan' }}</span>
                            <span class="shrink-0">Rp {{ number_format($l['harga_dasar'], 0, ',', '.') }}</span>
                        </div>
                        @if ($l['siswa_tambahan'] > 0)
                            <div class="flex justify-between gap-3">
                                <span>Siswa tambahan ({{ $l['siswa_tambahan'] }} &times; Rp {{ number_format($l['harga_per_siswa_tambahan'], 0, ',', '.') }})</span>
                                <span class="shrink-0">Rp {{ number_format($l['biaya_siswa_tambahan'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if ($l['diskon_persen'] > 0)
                            <div class="flex justify-between gap-3 text-success-600">
                                <span>Diskon volume {{ $l['diskon_persen'] }}%</span>
                                <span class="shrink-0">&minus; Rp {{ number_format($l['akses_platform_sebelum_diskon'] - $l['akses_platform'], 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between items-center mt-2 pt-2 border-t border-gray-100 dark:border-gray-800 font-medium text-gray-900 dark:text-white">
                        <span>Akses Platform</span>
                        <span>Rp {{ number_format($l['akses_platform'], 0, ',', '.') }}</span>
                    </div>

                    @if ($modulAktifList->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5 mt-3">
                            @foreach ($modulTampil as $m)
                                <span class="text-xs px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                    {{ $m['nama'] }}
                                </span>
                            @endforeach
                            @if ($modulSisa > 0)
                                <span class="text-xs px-2.5 py-1 rounded-full bg-gray-50 dark:bg-gray-800/50 text-gray-400 dark:text-gray-500">
                                    +{{ $modulSisa }} lainnya
                                </span>
                            @endif
                        </div>
                        @if ($semuaModulTermasukPaketFull)
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5 flex items-center gap-1">
                                <x-heroicon-o-check class="w-3.5 h-3.5" />
                                Semua modul termasuk Paket Full, tidak ada biaya tambahan
                            </p>
                        @endif
                    @endif

                    <div class="flex justify-between items-baseline mt-3 pt-2 border-t border-gray-100 dark:border-gray-800 mt-auto">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Subtotal lembaga</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">Rp {{ number_format($l['subtotal'], 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
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
            @endif
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

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
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
            @endphp
            <div class="flex items-center justify-between text-sm border-b border-gray-100 dark:border-gray-700 py-3 last:border-0">
                <div>
                    <div class="font-medium text-gray-800 dark:text-gray-100">
                        {{ $sub->plan->nama ?? '—' }}
                        <span class="text-xs font-normal text-gray-400">({{ $sub->isTahunan() ? 'Tahunan' : 'Bulanan' }})</span>
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

</x-filament-panels::page>
