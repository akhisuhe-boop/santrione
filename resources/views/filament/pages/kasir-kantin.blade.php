<x-filament-panels::page>

    <script src="https://unpkg.com/html5-qrcode"></script>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ================================================= --}}
        {{-- SCANNER + SISWA + PREVIEW PRODUK --}}
        {{-- ================================================= --}}
        <div class="lg:col-span-2 space-y-4">

            @if (! $siswaTerpilih)

                {{-- BELUM ADA SISWA — minta scan kartu siswa dulu --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-qr-code class="w-5 h-5" />
                            Scan Kartu Siswa
                        </div>
                    </x-slot>

                    <div id="reader" class="rounded-2xl overflow-hidden"></div>

                    <div class="mt-3 flex items-center gap-2">
                        <x-filament::input.wrapper class="flex-1">
                            <x-filament::input
                                type="text"
                                id="manual-input"
                                placeholder="Atau ketik NIS / kode manual, lalu Enter..."
                            />
                        </x-filament::input.wrapper>
                    </div>

                    <p class="text-xs text-gray-400 mt-2">
                        Arahkan kartu/QR siswa ke kamera. Kalau kamera tidak tersedia, ketik NIS manual di atas.
                    </p>
                </x-filament::section>

            @else

                {{-- SISWA SUDAH DIPILIH --}}
                <x-filament::section>

                    <div class="flex items-center justify-between gap-4">

                        <div class="flex items-center gap-4">

                            @if ($siswaTerpilih['foto'])
                                <img src="{{ asset('storage/' . $siswaTerpilih['foto']) }}" class="w-16 h-16 rounded-2xl object-cover">
                            @else
                                <div class="w-16 h-16 rounded-2xl bg-primary-50 dark:bg-primary-500/10 flex items-center justify-center text-primary-500">
                                    <x-heroicon-o-user class="w-8 h-8" />
                                </div>
                            @endif

                            <div>
                                <div class="font-bold text-lg text-gray-900 dark:text-white">{{ $siswaTerpilih['nama'] }}</div>
                                <div class="text-sm text-gray-500">NIS {{ $siswaTerpilih['nis'] }} &middot; {{ $siswaTerpilih['kelas'] }} &middot; {{ $siswaTerpilih['lembaga'] }}</div>
                                <div class="text-sm font-semibold text-primary-600 mt-1">
                                    Saldo: Rp {{ number_format($siswaTerpilih['saldo'], 0, ',', '.') }}
                                </div>
                            </div>

                        </div>

                        <x-filament::button color="gray" outlined wire:click="gantiSiswa" icon="heroicon-o-arrow-path">
                            Ganti Siswa
                        </x-filament::button>

                    </div>

                </x-filament::section>

                {{-- SCAN PRODUK --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-shopping-bag class="w-5 h-5" />
                            Scan Produk
                        </div>
                    </x-slot>

                    <div id="reader" class="rounded-2xl overflow-hidden"></div>

                    <div class="mt-3">
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                id="manual-input"
                                placeholder="Atau ketik barcode produk manual, lalu Enter..."
                            />
                        </x-filament::input.wrapper>
                    </div>

                    {{-- PREVIEW SCAN TERAKHIR --}}
                    @if ($previewProduk)
                        <div class="mt-4 flex items-center gap-3 rounded-2xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 p-3">

                            @if ($previewProduk['gambar'])
                                <img src="{{ asset('storage/' . $previewProduk['gambar']) }}" class="w-12 h-12 rounded-xl object-cover">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-white dark:bg-white/10 flex items-center justify-center text-emerald-500">
                                    <x-heroicon-o-check-circle class="w-6 h-6" />
                                </div>
                            @endif

                            <div class="flex-1">
                                <div class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ $previewProduk['nama'] }} ditambahkan</div>
                                <div class="text-xs text-emerald-600 dark:text-emerald-400">
                                    Rp {{ number_format($previewProduk['harga'], 0, ',', '.') }}
                                    @if ($previewProduk['stok'] !== null)
                                        &middot; sisa stok {{ $previewProduk['stok'] }}
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endif

                </x-filament::section>

            @endif

        </div>

        {{-- ================================================= --}}
        {{-- KERANJANG --}}
        {{-- ================================================= --}}
        <div>

            <x-filament::section class="sticky top-4">

                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-shopping-cart class="w-5 h-5" />
                        Keranjang
                    </div>
                </x-slot>

                <div class="space-y-3 mb-4 {{ count($cart) > 4 ? 'max-h-72 overflow-y-auto pr-1' : '' }}">

                    @forelse ($cart as $item)

                        <div class="flex items-center justify-between gap-3 text-sm">

                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 dark:text-white truncate">{{ $item['nama'] }}</div>
                                <div class="text-xs text-gray-400">Rp {{ number_format($item['harga'], 0, ',', '.') }}</div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <button
                                    type="button"
                                    wire:click="kurangiKeranjang({{ $item['id'] }})"
                                    class="w-7 h-7 rounded-full flex items-center justify-center bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/20 transition">
                                    <x-heroicon-o-minus class="w-3.5 h-3.5" />
                                </button>

                                <span class="w-6 text-center font-semibold text-sm">{{ $item['qty'] }}</span>

                                <button
                                    type="button"
                                    wire:click="tambahKeranjang({{ $item['id'] }})"
                                    class="w-7 h-7 rounded-full flex items-center justify-center bg-primary-50 dark:bg-primary-500/10 text-primary-600 hover:bg-primary-100 dark:hover:bg-primary-500/20 transition">
                                    <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                </button>
                            </div>

                        </div>

                    @empty

                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <x-heroicon-o-shopping-cart class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" />
                            <div class="text-xs text-gray-400">
                                {{ $siswaTerpilih ? 'Scan produk untuk menambahkan.' : 'Scan kartu siswa dulu.' }}
                            </div>
                        </div>

                    @endforelse

                </div>

                <div class="flex items-center justify-between font-semibold text-base border-t border-gray-100 dark:border-white/10 pt-4 mb-4">
                    <span class="text-gray-500 text-sm font-medium">Total Bayar</span>
                    <span class="text-primary-600 text-lg">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                </div>

                @if ($siswaTerpilih && $this->total > $siswaTerpilih['saldo'])
                    <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 p-3 text-xs text-red-600 dark:text-red-400">
                        Saldo tidak cukup — kurang Rp {{ number_format($this->total - $siswaTerpilih['saldo'], 0, ',', '.') }}
                    </div>
                @endif

                <x-filament::button
                    wire:click="checkout"
                    icon="heroicon-o-check-circle"
                    color="primary"
                    size="lg"
                    :disabled="! $siswaTerpilih || empty($cart)"
                    class="w-full justify-center">
                    Bayar (Wallet)
                </x-filament::button>

            </x-filament::section>

        </div>

    </div>

    <script>
        document.addEventListener('livewire:init', () => {

            let scanner = null;
            let scanning = false;
            let lastScan = '';
            let lastScanTime = 0;

            async function startScanner() {

                const readerEl = document.getElementById('reader');
                if (!readerEl || scanning) return;

                try {

                    const cameras = await Html5Qrcode.getCameras();
                    if (!cameras.length) return;

                    scanner = new Html5Qrcode('reader');
                    scanning = true;

                    await scanner.start(
                        cameras[0].id,
                        { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.7778 },
                        (decoded) => {
                            const now = Date.now();
                            // Debounce: jangan proses kode yang sama dalam 3 detik
                            // (kamera bisa baca kode yang sama berkali-kali per detik).
                            if (decoded === lastScan && (now - lastScanTime) < 3000) return;
                            lastScan = decoded;
                            lastScanTime = now;

                            @this.call('handleScan', decoded);
                        },
                        () => {}
                    );

                } catch (e) {
                    console.warn('Kamera tidak tersedia, pakai input manual saja.', e);
                }
            }

            function stopScanner() {
                if (scanner && scanning) {
                    scanner.stop().catch(() => {});
                    scanning = false;
                    scanner = null;
                }
            }

            function bindManualInput() {
                const input = document.getElementById('manual-input');
                if (!input) return;

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && input.value.trim() !== '') {
                        @this.call('handleScan', input.value.trim());
                        input.value = '';
                    }
                });
            }

            // Restart scanner tiap kali Livewire re-render (mis. setelah
            // scan siswa, DOM #reader diganti karena section-nya beda).
            Livewire.hook('morph.updated', () => {
                stopScanner();
                setTimeout(() => {
                    startScanner();
                    bindManualInput();
                }, 300);
            });

            startScanner();
            bindManualInput();
        });
    </script>

</x-filament-panels::page>
