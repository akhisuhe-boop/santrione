<x-filament-panels::page>

    <script src="https://unpkg.com/html5-qrcode"></script>

    {{--
        PENTING: halaman ini pakai <style> CSS polos (bukan Tailwind
        utility classes) untuk semua spacing/ukuran/layout krusial.
        Alasannya: Tailwind di Filament di-compile sekali lewat build
        step (bukan CDN), jadi class baru di file blade baru tidak
        otomatis "hidup" sampai di-build ulang. CSS polos di bawah ini
        selalu jalan di browser manapun tanpa perlu build apapun.
    --}}
    <style>
        .kk-section-body { padding: 4px 0; }
        .kk-gap-sm { margin-top: 10px; }
        .kk-gap-md { margin-top: 16px; }
        .kk-gap-lg { margin-top: 24px; }
        .kk-hint { font-size: 12px; color: #9ca3af; margin-top: 8px; }
        .kk-input {
            width: 100%; box-sizing: border-box; padding: 10px 14px;
            border: 1px solid #e5e7eb; border-radius: 12px; font-size: 14px;
            background: transparent;
        }
        .dark .kk-input { border-color: rgba(255,255,255,.1); color: #fff; }

        #reader-siswa, #reader-produk {
            width: 280px; height: 280px; max-width: 100%;
            margin: 0 auto; border-radius: 16px; overflow: hidden;
            background: #0f172a; display: flex; align-items: center; justify-content: center;
        }
        #reader-siswa video, #reader-produk video { width: 100% !important; height: 100% !important; object-fit: cover !important; }
        .kk-reader-placeholder { color: #64748b; font-size: 12px; text-align: center; padding: 12px; }

        .kk-row { display: flex; align-items: center; }
        .kk-row-between { display: flex; align-items: center; justify-content: space-between; }
        .kk-gap-3 { gap: 12px; }
        .kk-gap-2 { gap: 8px; }

        .kk-card {
            border: 1px solid #e5e7eb; border-radius: 16px; padding: 16px;
            background: #fff;
        }
        .dark .kk-card { border-color: rgba(255,255,255,.1); background: #1a1f2e; }

        .kk-card-highlight {
            border: 1px solid #a7f3d0; border-radius: 16px; padding: 12px;
            background: #ecfdf5; display: flex; align-items: center; gap: 12px;
        }
        .dark .kk-card-highlight { border-color: rgba(16,185,129,.3); background: rgba(16,185,129,.08); }

        .kk-avatar { border-radius: 16px; object-fit: cover; flex-shrink: 0; }
        .kk-name { font-weight: 700; font-size: 17px; color: #111827; }
        .dark .kk-name { color: #fff; }
        .kk-meta { font-size: 13px; color: #6b7280; margin-top: 3px; }
        .kk-saldo { font-size: 14px; font-weight: 700; color: #00A39D; margin-top: 6px; }
        .kk-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; font-weight: 600; padding: 3px 10px;
            border-radius: 999px; background: #f0fdfa; color: #00A39D;
            border: 1px solid #ccfbf1;
        }

        .kk-cart-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 14px; padding: 8px 0; }
        .kk-qty-btn {
            width: 28px; height: 28px; border-radius: 999px; display: flex;
            align-items: center; justify-content: center; border: none; cursor: pointer;
        }

        .kk-grid { display: grid; grid-template-columns: 1fr; gap: 24px; }
        @media (min-width: 1024px) {
            .kk-grid { grid-template-columns: 2fr 1fr; }
        }
    </style>

    <div class="kk-grid">

        <div style="display:grid; gap:16px;">

            @if (! $siswaTerpilih)

                {{-- BELUM ADA SISWA --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="kk-row kk-gap-2">
                            <x-heroicon-o-qr-code style="width:20px;height:20px;" />
                            Scan Kartu Siswa
                        </div>
                    </x-slot>

                    <div class="kk-section-body">

                        <div id="reader-siswa">
                            <div class="kk-reader-placeholder" id="reader-siswa-placeholder">Mengaktifkan kamera...</div>
                        </div>

                        <div class="kk-gap-md">
                            <input
                                type="text"
                                id="manual-input-siswa"
                                class="kk-input"
                                placeholder="Atau ketik NIS / kode manual, lalu Enter...">
                        </div>

                        <p class="kk-hint">Arahkan kartu/QR siswa ke kamera. Kalau kamera tidak tersedia, ketik NIS manual di atas.</p>

                    </div>
                </x-filament::section>

            @else

                {{-- SISWA SUDAH DIPILIH --}}
                <x-filament::section>
                    <div class="kk-section-body">
                        <div class="kk-row-between">

                            <div class="kk-row kk-gap-3">

                                @if ($siswaTerpilih['foto'])
                                    <img src="{{ asset('storage/' . $siswaTerpilih['foto']) }}" class="kk-avatar" style="width:88px;height:88px;">
                                @else
                                    <div class="kk-avatar" style="width:88px;height:88px;background:#f0fdfa;display:flex;align-items:center;justify-content:center;color:#00A39D;">
                                        <x-heroicon-o-user style="width:36px;height:36px;" />
                                    </div>
                                @endif

                                <div>
                                    <div class="kk-name">{{ $siswaTerpilih['nama'] }}</div>
                                    <div class="kk-meta">NIS {{ $siswaTerpilih['nis'] }}</div>
                                    <div class="kk-meta">{{ $siswaTerpilih['kelas'] }} &middot; {{ $siswaTerpilih['lembaga'] }}</div>
                                    <div class="kk-saldo">Saldo: Rp {{ number_format($siswaTerpilih['saldo'], 0, ',', '.') }}</div>
                                </div>

                            </div>

                            <x-filament::button color="gray" outlined wire:click="gantiSiswa" icon="heroicon-o-arrow-path">
                                Ganti Siswa
                            </x-filament::button>

                        </div>
                    </div>
                </x-filament::section>

                {{-- SCAN PRODUK --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="kk-row kk-gap-2">
                            <x-heroicon-o-shopping-bag style="width:20px;height:20px;" />
                            Scan Produk
                        </div>
                    </x-slot>

                    <div class="kk-section-body">

                        <div id="reader-produk">
                            <div class="kk-reader-placeholder" id="reader-produk-placeholder">Mengaktifkan kamera...</div>
                        </div>

                        <div class="kk-gap-md">
                            <input
                                type="text"
                                id="manual-input-produk"
                                class="kk-input"
                                placeholder="Atau ketik barcode produk manual, lalu Enter...">
                        </div>

                        @if ($previewProduk)
                            <div class="kk-gap-md kk-card-highlight">

                                @if ($previewProduk['gambar'])
                                    <img src="{{ asset('storage/' . $previewProduk['gambar']) }}" style="width:52px;height:52px;border-radius:12px;object-fit:cover;flex-shrink:0;">
                                @else
                                    <div style="width:52px;height:52px;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;color:#10b981;flex-shrink:0;">
                                        <x-heroicon-o-check-circle style="width:24px;height:24px;" />
                                    </div>
                                @endif

                                <div>
                                    <div style="font-size:14px;font-weight:600;color:#065f46;">{{ $previewProduk['nama'] }} ditambahkan</div>
                                    <div style="font-size:12px;color:#059669;margin-top:2px;">
                                        Rp {{ number_format($previewProduk['harga'], 0, ',', '.') }}
                                        @if ($previewProduk['stok'] !== null)
                                            &middot; sisa stok {{ $previewProduk['stok'] }}
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @endif

                    </div>
                </x-filament::section>

            @endif

        </div>

        {{-- KERANJANG --}}
        <div>

            <x-filament::section>

                <x-slot name="heading">
                    <div class="kk-row kk-gap-2">
                        <x-heroicon-o-shopping-cart style="width:20px;height:20px;" />
                        Keranjang
                    </div>
                </x-slot>

                <div class="kk-section-body">

                    <div style="max-height:280px; overflow-y:auto;">

                        @forelse ($cart as $item)

                            <div class="kk-cart-item">

                                <div style="flex:1; min-width:0;">
                                    <div style="font-weight:500; color:#111827;" class="dark:text-white">{{ $item['nama'] }}</div>
                                    <div style="font-size:12px; color:#9ca3af;">Rp {{ number_format($item['harga'], 0, ',', '.') }}</div>
                                </div>

                                <div class="kk-row kk-gap-2">
                                    <button type="button" wire:click="kurangiKeranjang({{ $item['id'] }})" class="kk-qty-btn" style="background:#f3f4f6;color:#374151;">
                                        <x-heroicon-o-minus style="width:14px;height:14px;" />
                                    </button>

                                    <span style="width:22px; text-align:center; font-weight:600; font-size:14px;">{{ $item['qty'] }}</span>

                                    <button type="button" wire:click="tambahKeranjang({{ $item['id'] }})" class="kk-qty-btn" style="background:#f0fdfa;color:#00A39D;">
                                        <x-heroicon-o-plus style="width:14px;height:14px;" />
                                    </button>
                                </div>

                            </div>

                        @empty

                            <div style="text-align:center; padding:24px 0; color:#9ca3af; font-size:12px;">
                                {{ $siswaTerpilih ? 'Scan produk untuk menambahkan.' : 'Scan kartu siswa dulu.' }}
                            </div>

                        @endforelse

                    </div>

                    <div class="kk-row-between kk-gap-md" style="border-top:1px solid #f3f4f6; padding-top:16px;">
                        <span style="font-size:13px; color:#6b7280; font-weight:500;">Total Bayar</span>
                        <span style="font-size:18px; font-weight:700; color:#00A39D;">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>

                    @if ($siswaTerpilih && $this->total > $siswaTerpilih['saldo'])
                        <div class="kk-gap-md" style="border-radius:12px; background:#fef2f2; border:1px solid #fecaca; padding:10px 12px; font-size:12px; color:#dc2626;">
                            Saldo tidak cukup — kurang Rp {{ number_format($this->total - $siswaTerpilih['saldo'], 0, ',', '.') }}
                        </div>
                    @endif

                    <div class="kk-gap-md">
                        <x-filament::button
                            wire:click="checkout"
                            icon="heroicon-o-check-circle"
                            color="primary"
                            size="lg"
                            :disabled="! $siswaTerpilih || empty($cart)"
                            style="width:100%; justify-content:center;">
                            Bayar (Wallet)
                        </x-filament::button>
                    </div>

                </div>

            </x-filament::section>

        </div>

    </div>

    <script>
        document.addEventListener('livewire:init', () => {

            let scanner = null;
            let currentReaderId = null;
            let lastScan = '';
            let lastScanTime = 0;

            async function startScanner(readerId) {

                const readerEl = document.getElementById(readerId);
                if (!readerEl) return;

                const placeholder = document.getElementById(readerId + '-placeholder');

                try {

                    const cameras = await Html5Qrcode.getCameras();

                    if (!cameras.length) {
                        if (placeholder) placeholder.textContent = 'Kamera tidak ditemukan — pakai input manual.';
                        return;
                    }

                    scanner = new Html5Qrcode(readerId);
                    currentReaderId = readerId;

                    await scanner.start(
                        cameras[0].id,
                        { fps: 10, qrbox: { width: 220, height: 220 }, aspectRatio: 1 },
                        (decoded) => {
                            const now = Date.now();
                            if (decoded === lastScan && (now - lastScanTime) < 3000) return;
                            lastScan = decoded;
                            lastScanTime = now;
                            @this.call('handleScan', decoded);
                        },
                        () => {}
                    );

                } catch (e) {
                    console.warn('Kamera tidak tersedia, pakai input manual saja.', e);
                    if (placeholder) placeholder.textContent = 'Kamera tidak tersedia — pakai input manual.';
                }
            }

            function stopScanner() {
                if (scanner) {
                    try { scanner.stop().catch(() => {}); } catch (e) {}
                    scanner = null;
                    currentReaderId = null;
                }
            }

            function bindManualInput(inputId) {
                const input = document.getElementById(inputId);
                if (!input) return;

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && input.value.trim() !== '') {
                        @this.call('handleScan', input.value.trim());
                        input.value = '';
                    }
                });
            }

            function boot() {
                stopScanner();

                setTimeout(() => {
                    if (document.getElementById('reader-siswa')) {
                        startScanner('reader-siswa');
                        bindManualInput('manual-input-siswa');
                    } else if (document.getElementById('reader-produk')) {
                        startScanner('reader-produk');
                        bindManualInput('manual-input-produk');
                    }
                }, 300);
            }

            Livewire.hook('morph.updated', () => boot());

            boot();
        });
    </script>

</x-filament-panels::page>
