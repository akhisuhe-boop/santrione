<x-filament-panels::page>

    <style>
        .kk-section-body { padding: 4px 0; }
        .kk-gap-sm { margin-top: 10px; }
        .kk-gap-md { margin-top: 16px; }
        .kk-gap-lg { margin-top: 24px; }
        .kk-hint { font-size: 12px; color: #9ca3af; margin-top: 8px; }
        .kk-input {
            width: 100%; box-sizing: border-box; padding: 14px 16px;
            border: 2px solid #e5e7eb; border-radius: 12px; font-size: 16px;
            background: transparent;
        }
        .kk-input:focus { outline: none; border-color: #00A39D; }
        .dark .kk-input { border-color: rgba(255,255,255,.15); color: #fff; }

        .kk-row { display: flex; align-items: center; }
        .kk-row-between { display: flex; align-items: center; justify-content: space-between; }
        .kk-gap-3 { gap: 12px; }
        .kk-gap-2 { gap: 8px; }

        .kk-card-highlight {
            border: 1px solid #a7f3d0; border-radius: 16px; padding: 12px;
            background: #ecfdf5; display: flex; align-items: center; gap: 12px;
        }
        .dark .kk-card-highlight { border-color: rgba(16,185,129,.3); background: rgba(16,185,129,.08); }

        .kk-avatar { border-radius: 16px; object-fit: cover; flex-shrink: 0; }
        .kk-name { font-weight: 700; font-size: 17px; color: #111827; }
        .dark .kk-name { color: #fff; }
        .kk-meta { font-size: 13px; color: #6b7280; margin-top: 3px; }
        .kk-saldo { font-size: 16px; font-weight: 800; color: #00A39D; margin-top: 6px; }

        .kk-cart-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; font-size: 14px; padding: 8px 0; }
        .kk-qty-btn {
            width: 28px; height: 28px; border-radius: 999px; display: flex;
            align-items: center; justify-content: center; border: none; cursor: pointer;
        }

        .kk-scan-icon {
            width: 52px; height: 52px; border-radius: 14px; background: #f0fdfa;
            color: #00A39D; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
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
                            <x-heroicon-o-identification style="width:20px;height:20px;" />
                            Input / Scan Kartu Siswa
                        </div>
                    </x-slot>

                    <div class="kk-section-body" style="padding-top:12px; padding-bottom:8px;">

                        <div class="kk-row kk-gap-3">

                            <div class="kk-scan-icon">
                                <x-heroicon-o-qr-code style="width:26px;height:26px;" />
                            </div>

                            <div style="flex:1;">
                                <input
                                    type="text"
                                    id="input-siswa"
                                    class="kk-input"
                                    autocomplete="off"
                                    placeholder="Ketik / scan NIS siswa, lalu Enter...">
                            </div>

                        </div>

                        <p class="kk-hint">Kursor sudah otomatis aktif di kolom ini — tinggal scan pakai barcode scanner, atau ketik NIS manual lalu tekan Enter.</p>

                    </div>
                </x-filament::section>

            @else

                {{-- SISWA SUDAH DIPILIH --}}
                <x-filament::section>
                    <div class="kk-section-body">
                        <div class="kk-row-between">

                            <div class="kk-row kk-gap-3">

                                @if ($siswaTerpilih['foto'])
                                    <img src="{{ Storage::disk('r2-public')->url($siswaTerpilih['foto']) }}" class="kk-avatar" style="width:88px;height:88px;">
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

                        @if ($siswaTerpilih['limit_harian'])

                            @php
                                $limit = $siswaTerpilih['limit_harian'];
                                $terpakai = $siswaTerpilih['terpakai_hari_ini'];
                                $sisaSaatIni = max(0, $limit - $terpakai);
                                $sisaSetelahKeranjang = $sisaSaatIni - $this->total;
                                $persen = $limit > 0 ? min(100, round((($terpakai + $this->total) / $limit) * 100)) : 0;
                                $warna = $sisaSetelahKeranjang < 0 ? '#dc2626' : ($persen >= 80 ? '#d97706' : '#00A39D');
                            @endphp

                            <div class="kk-gap-md" style="border-radius:14px; background:#f8fafc; border:1px solid #e5e7eb; padding:12px 14px;">

                                <div class="kk-row-between" style="font-size:12px; color:#6b7280; margin-bottom:6px;">
                                    <span>Limit Belanja Harian</span>
                                    <span style="font-weight:700; color:{{ $warna }};">
                                        Sisa Rp {{ number_format($sisaSetelahKeranjang, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div style="height:6px; border-radius:999px; background:#e5e7eb; overflow:hidden;">
                                    <div style="height:100%; width:{{ $persen }}%; background:{{ $warna }}; border-radius:999px; transition:width .2s;"></div>
                                </div>

                                <div style="font-size:11px; color:#9ca3af; margin-top:6px;">
                                    Sudah dipakai hari ini Rp {{ number_format($terpakai, 0, ',', '.') }} dari limit Rp {{ number_format($limit, 0, ',', '.') }}
                                    @if ($sisaSetelahKeranjang < 0)
                                        <span style="color:#dc2626; font-weight:600;"> — keranjang ini melebihi limit!</span>
                                    @endif
                                </div>

                            </div>

                        @endif
                    </div>
                </x-filament::section>

                {{-- SCAN PRODUK --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="kk-row kk-gap-2">
                            <x-heroicon-o-shopping-bag style="width:20px;height:20px;" />
                            Input / Scan Produk
                        </div>
                    </x-slot>

                    <div class="kk-section-body" style="padding-top:12px; padding-bottom:8px;">

                        <div class="kk-row kk-gap-3">

                            <div class="kk-scan-icon">
                                <x-heroicon-o-shopping-bag style="width:26px;height:26px;" />
                            </div>

                            <div style="flex:1;">
                                <input
                                    type="text"
                                    id="input-produk"
                                    class="kk-input"
                                    autocomplete="off"
                                    placeholder="Ketik / scan barcode produk, lalu Enter...">
                            </div>

                        </div>

                        <p class="kk-hint">Scan barcode produk satu-satu — tiap scan otomatis masuk keranjang di kanan.</p>

                        @if ($previewProduk)
                            <div class="kk-gap-md kk-card-highlight" style="text-align:left;">

                                @if ($previewProduk['gambar'])
                                    <img src="{{ Storage::disk('r2-public')->url($previewProduk['gambar']) }}" style="width:52px;height:52px;border-radius:12px;object-fit:cover;flex-shrink:0;">
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

            function bindInput(inputId) {
                const input = document.getElementById(inputId);
                if (!input) return false;

                input.focus();

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' && input.value.trim() !== '') {
                        @this.call('handleScan', input.value.trim());
                        input.value = '';
                    }
                });

                return true;
            }

            function boot() {
                if (!bindInput('input-siswa')) {
                    bindInput('input-produk');
                }
            }

            // Auto-focus lagi tiap Livewire selesai re-render (mis.
            // setelah siswa/produk berhasil discan), supaya kasir bisa
            // langsung lanjut scan berikutnya tanpa klik kolom lagi.
            Livewire.hook('morph.updated', () => setTimeout(boot, 100));

            boot();
        });
    </script>

</x-filament-panels::page>
