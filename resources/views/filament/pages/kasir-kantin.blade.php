<x-filament-panels::page>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ================================================= --}}
        {{-- PRODUK --}}
        {{-- ================================================= --}}
        <div class="lg:col-span-2 space-y-4">

            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari produk..."
                    icon="heroicon-o-magnifying-glass"
                />
            </x-filament::input.wrapper>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">

                @forelse ($this->produk as $produk)

                    @php
                        $diKeranjang = $cart[$produk->id]['qty'] ?? 0;
                    @endphp

                    <button
                        type="button"
                        wire:click="tambahKeKeranjang({{ $produk->id }})"
                        @if ($produk->habisStok()) disabled @endif
                        class="group relative text-left rounded-2xl border bg-white dark:bg-gray-900 p-3 shadow-sm transition
                            hover:shadow-md hover:-translate-y-0.5
                            disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-y-0
                            {{ $diKeranjang > 0 ? 'border-primary-500 ring-1 ring-primary-500' : 'border-gray-200 dark:border-white/10' }}">

                        @if ($diKeranjang > 0)
                            <span class="absolute -top-2 -right-2 z-10">
                                <x-filament::badge color="primary" size="lg">
                                    {{ $diKeranjang }}
                                </x-filament::badge>
                            </span>
                        @endif

                        @if ($produk->gambar)
                            <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-24 object-cover rounded-xl mb-2.5">
                        @else
                            <div class="w-full h-24 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-white/5 dark:to-white/10 flex items-center justify-center mb-2.5 text-gray-300 dark:text-gray-600 group-hover:text-primary-400 transition">
                                <x-heroicon-o-shopping-bag class="w-9 h-9" />
                            </div>
                        @endif

                        <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $produk->nama }}</div>
                        <div class="text-sm font-bold text-primary-600 mt-0.5">Rp {{ number_format($produk->harga, 0, ',', '.') }}</div>

                        <div class="flex items-center justify-between mt-1.5">
                            @if ($produk->kategori)
                                <span class="text-[11px] text-gray-400">{{ $produk->kategori }}</span>
                            @else
                                <span></span>
                            @endif

                            @if ($produk->stok !== null)
                                <span class="text-[11px] font-medium {{ $produk->stok <= 5 ? 'text-amber-500' : 'text-gray-400' }}">
                                    Stok {{ $produk->stok }}
                                </span>
                            @endif
                        </div>

                    </button>

                @empty

                    <div class="col-span-full">
                        <div class="flex flex-col items-center justify-center py-16 text-center rounded-2xl border border-dashed border-gray-200 dark:border-white/10">
                            <x-heroicon-o-shopping-bag class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" />
                            <div class="text-sm font-medium text-gray-500">Belum ada produk aktif untuk lembaga ini.</div>
                            <div class="text-xs text-gray-400 mt-1">Tambahkan produk lewat menu e-Kantin &rarr; Produk.</div>
                        </div>
                    </div>

                @endforelse

            </div>

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
                                    wire:click="tambahKeKeranjang({{ $item['id'] }})"
                                    class="w-7 h-7 rounded-full flex items-center justify-center bg-primary-50 dark:bg-primary-500/10 text-primary-600 hover:bg-primary-100 dark:hover:bg-primary-500/20 transition">
                                    <x-heroicon-o-plus class="w-3.5 h-3.5" />
                                </button>
                            </div>

                        </div>

                    @empty

                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <x-heroicon-o-shopping-cart class="w-8 h-8 text-gray-300 dark:text-gray-600 mb-2" />
                            <div class="text-xs text-gray-400">Belum ada item — klik produk di kiri untuk menambahkan.</div>
                        </div>

                    @endforelse

                </div>

                <div class="flex items-center justify-between font-semibold text-base border-t border-gray-100 dark:border-white/10 pt-4 mb-4">
                    <span class="text-gray-500 text-sm font-medium">Total Bayar</span>
                    <span class="text-primary-600 text-lg">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                </div>

                <div class="space-y-3 mb-4">

                    <div>
                        <label class="text-xs font-medium text-gray-500 mb-1.5 block">Metode Bayar</label>
                        <x-filament::input.wrapper>
                            <select wire:model.live="metode" class="fi-select-input block w-full border-none bg-transparent py-1.5 text-sm focus:ring-0">
                                <option value="wallet">💳 Wallet Siswa</option>
                                <option value="tunai">💵 Tunai</option>
                            </select>
                        </x-filament::input.wrapper>
                    </div>

                    @if ($metode === 'wallet')
                        <div>
                            <label class="text-xs font-medium text-gray-500 mb-1.5 block">Siswa</label>
                            <x-filament::input.wrapper>
                                <select wire:model="siswa_id" class="fi-select-input block w-full border-none bg-transparent py-1.5 text-sm focus:ring-0">
                                    <option value="">Pilih siswa...</option>
                                    @foreach ($this->siswaOptions as $s)
                                        <option value="{{ $s->id }}">{{ $s->nama_lengkap }} &middot; {{ $s->kelas->nama ?? '-' }}</option>
                                    @endforeach
                                </select>
                            </x-filament::input.wrapper>
                        </div>
                    @endif

                </div>

                <x-filament::button
                    wire:click="checkout"
                    icon="heroicon-o-check-circle"
                    color="primary"
                    size="lg"
                    class="w-full justify-center">
                    Bayar Sekarang
                </x-filament::button>

            </x-filament::section>

        </div>

    </div>

</x-filament-panels::page>
