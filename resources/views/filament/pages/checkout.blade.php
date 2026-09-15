<x-filament-panels::page>

    @php
        $estimasi = $this->getEstimasi();
        $lembagas = $this->getLembagas();
        $modulOptions = $this->getModulOptions();
        $subAktif = $this->getSubscriptionAktif();
        $paketFullAktif = $this->isPaketFullAktif();
        $tahunanDipilih = $this->isTahunanDipilih();
    @endphp

    <div class="mb-2">
        <a href="{{ \App\Filament\Pages\Langganan::getUrl(tenant: $this->getYayasan()) }}" class="text-sm text-gray-500 hover:text-primary-600 flex items-center gap-1">
            <x-heroicon-o-arrow-left class="w-4 h-4" /> Kembali ke Langganan
        </a>
    </div>

    {{-- TOGGLE SIKLUS BILLING --}}
    @php
        $diskonTahunanBadge = (int) (\App\Models\SubscriptionPlan::where('slug', 'akses-platform')->value('diskon_tahunan_persen') ?? 0);
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
                @if ($diskonTahunanBadge > 0)
                    <span class="text-[10px] font-bold bg-success-100 text-success-700 px-2 py-0.5 rounded-full">
                        Hemat {{ $diskonTahunanBadge }}%
                    </span>
                @endif
            </button>
        </div>
        @if ($tahunanDipilih)
            <p class="text-xs text-gray-400">Bayar 1 tahun sekaligus, langsung dapat diskon.</p>
        @endif
    </div>

    {{-- TAGIHAN PENDING --}}
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
    @endif

    {{-- PILIH MODUL (kiri) + RINCIAN TAGIHAN (kanan) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">

    {{-- PILIH MODUL --}}
    @if ($lembagas->isEmpty())
        <x-filament::section>
            <div class="text-center py-8">
                <x-heroicon-o-building-office-2 class="w-10 h-10 mx-auto text-gray-300" />
                <div class="font-semibold text-gray-900 dark:text-white mt-3">Belum ada Lembaga</div>
                <p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">
                    Buat Lembaga (unit sekolah) dulu di menu <strong>Master Data &rarr; Lembaga</strong>, baru Anda bisa pilih modul yang mau diaktifkan.
                </p>
                <a href="{{ \App\Filament\Resources\LembagaResource::getUrl('create', tenant: $this->getYayasan()) }}" class="inline-block mt-4">
                    <x-filament::button icon="heroicon-o-plus">Buat Lembaga</x-filament::button>
                </a>
            </div>
        </x-filament::section>
    @else
        <x-filament::section heading="Pilih Modul" icon="heroicon-o-squares-2x2">
            <p class="text-xs text-gray-400 mb-3">Modul berlaku untuk SEMUA Lembaga di Yayasan Anda ({{ $lembagas->pluck('nama')->implode(', ') }}), dihitung per siswa.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach ($modulOptions as $modul)
                    @php $aktif = $paketFullAktif ? true : $this->isModuleActive($modul->id); @endphp
                    <button
                        type="button"
                        wire:key="modul-{{ $modul->id }}-{{ $aktif ? 'on' : 'off' }}"
                        @if (! $paketFullAktif) wire:click="toggleModule({{ $modul->id }})" @endif
                        {{ $paketFullAktif ? 'disabled' : '' }}
                        class="text-left rounded-xl border-2 p-4 transition-all {{ $aktif ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-gray-200 dark:border-gray-700' }} {{ $paketFullAktif ? 'opacity-70' : '' }}"
                    >
                        <div class="flex items-center justify-between">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $modul->nama }}</div>
                            @if ($aktif)
                                <x-heroicon-s-check-circle class="w-5 h-5 text-primary-600 shrink-0" />
                            @else
                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 shrink-0"></div>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Rp {{ number_format($modul->harga_per_siswa, 0, ',', '.') }} / siswa / bulan</div>
                    </button>
                @endforeach
            </div>

            @if ($paketFullAktif)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-3 flex items-center gap-1">
                    <x-heroicon-o-check class="w-3.5 h-3.5" />
                    Paket Full aktif — semua modul otomatis aktif.
                </p>
            @endif
        </x-filament::section>
    @endif

    {{-- RINCIAN TAGIHAN (per-siswa) --}}
    <x-filament::section :heading="$tahunanDipilih ? 'Tagihan Tahunan' : 'Tagihan Bulanan'" icon="heroicon-o-document-text">

        <div class="flex items-center justify-between flex-wrap gap-3 mb-4 text-sm">
            <div class="text-gray-500">{{ $estimasi['total_siswa'] }} siswa se-Yayasan ({{ $lembagas->count() }} lembaga)</div>
            @if ($paketFullAktif)
                <x-filament::badge color="warning" icon="heroicon-o-sparkles">Paket Full Aktif</x-filament::badge>
            @endif
        </div>

        <table class="w-full text-sm mb-4">
            <tbody>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-2 text-gray-600 dark:text-gray-300">Akses Platform (dasar)</td>
                    <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($estimasi['harga_dasar_per_siswa'], 0, ',', '.') }} / siswa</td>
                </tr>
                @foreach ($estimasi['rincian_modul'] as $m)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="py-2 text-gray-600 dark:text-gray-300">Modul {{ $m['nama'] }}</td>
                        <td class="py-2 text-right text-gray-900 dark:text-white">Rp {{ number_format($m['harga_per_siswa'], 0, ',', '.') }} / siswa</td>
                    </tr>
                @endforeach
                @foreach ($estimasi['rincian_modul_gratis'] as $m)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="py-2 text-gray-400">{{ $m['nama'] }} <span class="text-xs">(gratis, dibebankan ke wali murid)</span></td>
                        <td class="py-2 text-right text-gray-400">Rp 0</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="py-2 font-semibold text-gray-900 dark:text-white">Total per Siswa</td>
                    <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($estimasi['rate_per_siswa'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="rounded-xl bg-primary-50 dark:bg-primary-500/10 p-4">
            <div class="flex justify-between gap-3 text-sm text-gray-500 dark:text-gray-400 mb-1">
                <span>Subtotal ({{ number_format($estimasi['rate_per_siswa'], 0, ',', '.') }} &times; {{ $estimasi['total_siswa'] }} siswa)</span>
                <span class="shrink-0">Rp {{ number_format($estimasi['subtotal_sebelum_diskon'], 0, ',', '.') }}</span>
            </div>

            @if ($estimasi['diskon_volume_persen'] > 0)
                <div class="flex justify-between gap-3 text-sm text-success-600 mb-1">
                    <span>Diskon Volume ({{ $estimasi['diskon_volume_persen'] }}%, {{ $estimasi['total_siswa'] }} siswa)</span>
                    <span class="shrink-0">&minus; Rp {{ number_format($estimasi['subtotal_sebelum_diskon'] - $estimasi['setelah_diskon_volume'], 0, ',', '.') }}</span>
                </div>
            @endif

            @if (($estimasi['diskon_paket_full_persen'] ?? 0) > 0)
                <div class="flex justify-between gap-3 text-sm text-warning-600 mb-1">
                    <span>Diskon Paket Full ({{ $estimasi['diskon_paket_full_persen'] }}%)</span>
                    <span class="shrink-0">&minus; Rp {{ number_format($estimasi['setelah_diskon_volume'] - ($estimasi['setelah_diskon_volume'] * (100 - $estimasi['diskon_paket_full_persen']) / 100), 0, ',', '.') }}</span>
                </div>
            @endif

            @if ($tahunanDipilih)
                <div class="flex justify-between gap-3 text-sm text-gray-500 dark:text-gray-400 mb-1">
                    <span>&times; 12 bulan</span>
                    <span class="shrink-0">Rp {{ number_format($estimasi['total_tahunan_sebelum_diskon'], 0, ',', '.') }}</span>
                </div>
                @if (($estimasi['promo_pendaftaran_persen'] ?? 0) > 0)
                    <div class="flex justify-between gap-3 text-sm text-accent-600 mb-1">
                        <span>Diskon Pendaftaran "{{ $estimasi['promo_pendaftaran_teks'] }}" ({{ $estimasi['promo_pendaftaran_persen'] }}%)<span class="block text-[11px] font-normal text-gray-400">Berlaku 1x untuk tagihan pertama ini saja</span></span>
                        <span class="shrink-0">&minus; Rp {{ number_format($estimasi['total_tahunan_sebelum_diskon'] - $estimasi['total'], 0, ',', '.') }}</span>
                    </div>
                @elseif ($estimasi['diskon_tahunan_persen'] > 0)
                    <div class="flex justify-between gap-3 text-sm text-success-600 mb-1">
                        <span>Diskon Tahunan ({{ $estimasi['diskon_tahunan_persen'] }}%)</span>
                        <span class="shrink-0">&minus; Rp {{ number_format($estimasi['total_tahunan_sebelum_diskon'] - $estimasi['total'], 0, ',', '.') }}</span>
                    </div>
                @endif
            @else
                @if (($estimasi['promo_pendaftaran_persen'] ?? 0) > 0)
                    <div class="flex justify-between gap-3 text-sm text-accent-600 mb-1">
                        <span>Diskon Pendaftaran "{{ $estimasi['promo_pendaftaran_teks'] }}" ({{ $estimasi['promo_pendaftaran_persen'] }}%)<span class="block text-[11px] font-normal text-gray-400">Berlaku 1x untuk tagihan pertama ini saja</span></span>
                        <span class="shrink-0">&minus; Rp {{ number_format($estimasi['subtotal_sebelum_promo'] - $estimasi['total'], 0, ',', '.') }}</span>
                    </div>
                @endif
            @endif

            @if (($estimasi['promo_kode_diskon_persen'] ?? 0) > 0)
                <div class="flex justify-between gap-3 text-sm text-primary-600 mb-1">
                    <span>Kode Promo "{{ $estimasi['promo_kode'] }}" ({{ $estimasi['promo_kode_diskon_persen'] }}%)</span>
                    <span class="shrink-0">&minus; Rp {{ number_format($estimasi['total_sebelum_promo_kode'] - $estimasi['total'], 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="flex justify-between items-center mt-2 pt-2 border-t border-primary-100 dark:border-primary-500/20">
                <p class="text-sm font-medium text-primary-700 dark:text-primary-300">Total Tagihan / {{ $tahunanDipilih ? 'tahun' : 'bulan' }}</p>
                <p class="text-2xl font-bold text-primary-600">Rp {{ number_format($estimasi['total'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- KODE PROMO --}}
        <div class="mt-4">
            @if ($kodePromoDiterapkan)
                <div class="flex items-center justify-between gap-3 rounded-xl border border-success-200 bg-success-50 dark:bg-success-500/10 px-4 py-3">
                    <div class="flex items-center gap-2 text-sm text-success-700 dark:text-success-400">
                        <x-heroicon-o-ticket class="w-4 h-4 shrink-0" />
                        Kode <strong>{{ $kodePromoDiterapkan }}</strong> diterapkan
                    </div>
                    <button type="button" wire:click="hapusKodePromo" class="text-xs text-gray-400 hover:text-danger-600">Hapus</button>
                </div>
            @else
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model="kodePromo"
                        placeholder="Punya kode promo?"
                        class="fi-input flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm uppercase"
                    >
                    <x-filament::button wire:click="terapkanKodePromo" color="gray" outlined>Terapkan</x-filament::button>
                </div>
            @endif
        </div>
    </x-filament::section>

    </div>

    {{-- PAKET FULL --}}
    @if (! $paketFullAktif)
        @php $estimasiPaketFull = $this->getEstimasiPaketFull(); @endphp
        <x-filament::section>
            <div class="flex items-center justify-between flex-wrap gap-4 {{ $estimasiPaketFull ? 'pb-4 border-b border-gray-100 dark:border-gray-700' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-warning-100 dark:bg-warning-500/10 p-3">
                        <x-heroicon-o-sparkles class="w-6 h-6 text-warning-600" />
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">Mau semua modul sekaligus?</div>
                        <div class="text-sm text-gray-500">Aktifkan Paket Full — dapat diskon ekstra dibanding pilih modul satu-satu.</div>
                    </div>
                </div>
                <x-filament::button wire:click="aktifkanPaketFull" color="warning">Aktifkan Paket Full</x-filament::button>
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
                        <div class="text-sm text-gray-500">Semua modul aktif otomatis.</div>
                    </div>
                </div>
                <x-filament::button wire:click="batalkanPaketFull" color="gray" outlined>Kembali Pilih Satu-satu</x-filament::button>
            </div>
        </x-filament::section>
    @endif

    {{-- METODE PEMBAYARAN + SUBMIT --}}
    @if ($this->shouldShowBayarButton() && ! $pendingUrl)
        <x-filament::section heading="Metode Pembayaran" icon="heroicon-o-wallet">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                <button
                    type="button"
                    wire:click="setMetodePembayaran('doku')"
                    class="text-left rounded-xl border-2 p-4 transition-all {{ $metodePembayaran === 'doku' ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-gray-200 dark:border-gray-700' }}"
                >
                    <div class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-credit-card class="w-5 h-5" /> DOKU (Otomatis)
                    </div>
                    <div class="text-xs text-gray-500 mt-1">VA semua bank / QRIS / E-Wallet / dll — langganan langsung aktif begitu bayar.</div>
                </button>

                <button
                    type="button"
                    wire:click="setMetodePembayaran('manual')"
                    class="text-left rounded-xl border-2 p-4 transition-all {{ $metodePembayaran === 'manual' ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10' : 'border-gray-200 dark:border-gray-700' }}"
                >
                    <div class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-banknotes class="w-5 h-5" /> Transfer Manual
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Transfer ke rekening Qinara, lalu upload bukti — diverifikasi admin (biasanya 1x24 jam kerja).</div>
                </button>
            </div>

            @if ($metodePembayaran === 'manual')
                <div class="rounded-xl bg-gray-50 dark:bg-gray-800/50 p-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Upload Bukti Transfer</label>
                    <input
                        type="file"
                        wire:model="buktiTransfer"
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                    >
                    <div wire:loading wire:target="buktiTransfer" class="text-xs text-gray-400 mt-1">Mengunggah...</div>
                    @error('buktiTransfer') <div class="text-xs text-danger-600 mt-1">{{ $message }}</div> @enderror
                    @if ($buktiTransfer)
                        <div class="text-xs text-success-600 mt-1 flex items-center gap-1">
                            <x-heroicon-o-check-circle class="w-3.5 h-3.5" /> File siap diupload: {{ $buktiTransfer->getClientOriginalName() }}
                        </div>
                    @endif
                    <p class="text-xs text-gray-400 mt-2">Format JPG/PNG/PDF, maksimal 5MB.</p>
                </div>
            @endif

            <x-filament::button wire:click="bayarSekarang" color="primary" icon="heroicon-o-credit-card" class="w-full sm:w-auto">
                {{ $metodePembayaran === 'manual' ? 'Kirim Bukti Transfer' : 'Bayar Sekarang' }}
            </x-filament::button>
        </x-filament::section>
    @endif

</x-filament-panels::page>
