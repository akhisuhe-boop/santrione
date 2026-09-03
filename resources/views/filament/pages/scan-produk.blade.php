<x-filament-panels::page>

    <style>
        .sp-input {
            width: 100%; box-sizing: border-box; padding: 14px 16px;
            border: 2px solid #e5e7eb; border-radius: 12px; font-size: 16px;
            background: transparent;
        }
        .sp-input:focus { outline: none; border-color: #00A39D; }
        .dark .sp-input { border-color: rgba(255,255,255,.15); color: #fff; }

        .sp-row { display: flex; align-items: center; }
        .sp-row-between { display: flex; align-items: center; justify-content: space-between; }
        .sp-gap-3 { gap: 12px; }
        .sp-gap-2 { gap: 8px; }
        .sp-hint { font-size: 12px; color: #9ca3af; margin-top: 8px; }

        .sp-scan-icon {
            width: 52px; height: 52px; border-radius: 14px; background: #f0fdfa;
            color: #00A39D; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .sp-label { font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; display:block; }
        .dark .sp-label { color: #d1d5db; }

        .sp-form-2kolom {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }
        @media (min-width: 640px) {
            .sp-form-2kolom {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>

    @if (! $kantinTerpilih)

        {{-- PILIH KANTIN --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="sp-row sp-gap-2">
                    <x-heroicon-o-building-storefront style="width:20px;height:20px;" />
                    Pilih Kantin
                </div>
            </x-slot>

            <div style="padding-top:8px;">

                @if (empty($daftarKantin))
                    <p class="sp-hint" style="font-size:13px;">Belum ada kantin aktif di tenant ini.</p>
                @else
                    <p class="sp-hint" style="margin-top:0; margin-bottom:12px;">Pilih kantin yang mau diisi/di-restok produknya.</p>

                    <div style="display:grid; gap:10px;">
                        @foreach ($daftarKantin as $id => $nama)
                            <button
                                type="button"
                                wire:click="pilihKantin({{ $id }})"
                                style="text-align:left; padding:14px 16px; border:2px solid #e5e7eb; border-radius:12px; background:transparent; cursor:pointer; font-weight:600; font-size:14px; display:flex; align-items:center; gap:10px;">
                                <x-heroicon-o-building-storefront style="width:18px;height:18px;color:#00A39D;" />
                                {{ $nama }}
                            </button>
                        @endforeach
                    </div>
                @endif

            </div>
        </x-filament::section>

    @else

        <div class="sp-row-between" style="margin-bottom:12px;">
            <span style="font-size:13px; color:#6b7280;">
                Kantin: <b style="color:#111827;">{{ $daftarKantin[$kantinTerpilih] ?? '-' }}</b>
            </span>

            @if (count($daftarKantin) > 1)
                <x-filament::button color="gray" outlined size="sm" wire:click="gantiKantin" icon="heroicon-o-arrow-path">
                    Ganti Kantin
                </x-filament::button>
            @endif
        </div>

        {{-- SCAN --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="sp-row sp-gap-2">
                    <x-heroicon-o-viewfinder-circle style="width:20px;height:20px;" />
                    Scan Barcode Produk
                </div>
            </x-slot>

            <div style="padding-top:12px; padding-bottom:8px;">

                <div class="sp-row sp-gap-3">
                    <div class="sp-scan-icon">
                        <x-heroicon-o-qr-code style="width:26px;height:26px;" />
                    </div>
                    <div style="flex:1;">
                        <input
                            type="text"
                            id="input-scan-produk"
                            class="sp-input"
                            autocomplete="off"
                            placeholder="Scan barcode produk (lama atau baru), lalu Enter...">
                    </div>
                </div>

                <p class="sp-hint">
                    Kalau barcode-nya <b>sudah terdaftar</b> — otomatis masuk mode tambah stok.
                    Kalau <b>belum pernah ada</b> — otomatis masuk mode daftarkan produk baru.
                </p>

            </div>
        </x-filament::section>

        {{-- MODE RESTOCK --}}
        @if ($mode === 'restock' && $produkDitemukan)

            <x-filament::section>
                <x-slot name="heading">
                    <div class="sp-row sp-gap-2" style="color:#0f766e;">
                        <x-heroicon-o-arrow-path-rounded-square style="width:20px;height:20px;" />
                        Tambah Stok — Produk Sudah Terdaftar
                    </div>
                </x-slot>

                <div style="padding-top:8px;">

                    <div class="sp-row sp-gap-3" style="background:#f0fdfa; border-radius:14px; padding:14px; margin-bottom:16px;">

                        @if ($produkDitemukan['gambar'])
                            <img src="{{ Storage::disk('r2-public')->url($produkDitemukan['gambar']) }}" style="width:56px;height:56px;border-radius:12px;object-fit:cover;flex-shrink:0;">
                        @else
                            <div style="width:56px;height:56px;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;color:#00A39D;flex-shrink:0;">
                                <x-heroicon-o-cube style="width:26px;height:26px;" />
                            </div>
                        @endif

                        <div>
                            <div style="font-weight:700; font-size:15px; color:#111827;">{{ $produkDitemukan['nama'] }}</div>
                            <div style="font-size:12px; color:#6b7280;">{{ $produkDitemukan['kategori'] ?? '-' }} &middot; Rp {{ number_format($produkDitemukan['harga'], 0, ',', '.') }}</div>
                            <div style="font-size:13px; color:#00A39D; font-weight:700; margin-top:2px;">
                                Stok sekarang: {{ $produkDitemukan['stok_sekarang'] ?? 'Tidak dilacak' }}
                            </div>
                        </div>

                    </div>

                    <label class="sp-label">Jumlah yang masuk (restok)</label>
                    <input
                        type="number"
                        min="1"
                        wire:model="jumlahTambahan"
                        wire:keydown.enter="simpanRestock"
                        class="sp-input"
                        autofocus
                        placeholder="mis. 24">

                    <div class="sp-row sp-gap-3" style="margin-top:16px;">
                        <x-filament::button wire:click="simpanRestock" icon="heroicon-o-check" style="flex:1; justify-content:center;">
                            Tambah Stok
                        </x-filament::button>
                        <x-filament::button color="gray" outlined wire:click="batal" icon="heroicon-o-x-mark">
                            Batal
                        </x-filament::button>
                    </div>

                </div>
            </x-filament::section>

        @endif

        {{-- MODE PRODUK BARU --}}
        @if ($mode === 'produk_baru')

            <x-filament::section>
                <x-slot name="heading">
                    <div class="sp-row sp-gap-2" style="color:#c2410c;">
                        <x-heroicon-o-sparkles style="width:20px;height:20px;" />
                        Daftarkan Produk Baru
                    </div>
                </x-slot>

                <div style="padding-top:8px;">

                    <p class="sp-hint" style="margin-top:0; margin-bottom:14px;">
                        Barcode <b>{{ $barcodeBaru }}</b> belum pernah terdaftar — isi detailnya untuk daftarkan sebagai produk baru.
                    </p>

                    <div class="sp-form-2kolom">

                        <div>
                            <label class="sp-label">Nama Produk</label>
                            <input type="text" wire:model="namaBaru" class="sp-input" autofocus placeholder="mis. Aqua 600ml">
                        </div>

                        <div>
                            <label class="sp-label">Kategori</label>
                            <input type="text" wire:model="kategoriBaru" class="sp-input" placeholder="Makanan / Minuman / Snack / dll">
                        </div>

                        <div>
                            <label class="sp-label">Harga Jual</label>
                            <input type="text" wire:model="hargaBaru" class="sp-input" placeholder="mis. 5000">
                        </div>

                        <div>
                            <label class="sp-label">Stok Awal (opsional)</label>
                            <input type="number" min="0" wire:model="stokAwalBaru" class="sp-input" placeholder="Kosongkan kalau tidak mau melacak stok">
                        </div>

                    </div>

                    <div class="sp-row sp-gap-3" style="margin-top:20px;">
                        <x-filament::button wire:click="simpanProdukBaru" icon="heroicon-o-check" style="flex:1; justify-content:center;">
                            Daftarkan Produk
                        </x-filament::button>
                        <x-filament::button color="gray" outlined wire:click="batal" icon="heroicon-o-x-mark">
                            Batal
                        </x-filament::button>
                    </div>

                </div>
            </x-filament::section>

        @endif

    @endif

    <script>
        document.addEventListener('livewire:init', () => {

            function boot() {
                const input = document.getElementById('input-scan-produk');
                if (!input) return;

                input.focus();

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && input.value.trim() !== '') {
                        @this.call('handleScan', input.value.trim());
                        input.value = '';
                    }
                });
            }

            Livewire.hook('morph.updated', () => setTimeout(boot, 100));

            boot();
        });
    </script>

</x-filament-panels::page>
