<x-filament-panels::page>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- PRODUK --}}
        <div class="lg:col-span-2">

            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari produk..."
                class="w-full rounded-xl border-gray-300 dark:bg-gray-800 mb-4">

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">

                @forelse ($this->produk as $produk)

                    <button
                        type="button"
                        wire:click="tambahKeKeranjang({{ $produk->id }})"
                        @if ($produk->habisStok()) disabled @endif
                        class="text-left rounded-2xl border border-gray-200 dark:border-white/10 p-3 hover:border-primary-500 transition disabled:opacity-40 disabled:cursor-not-allowed">

                        @if ($produk->gambar)
                            <img src="{{ asset('storage/' . $produk->gambar) }}" class="w-full h-24 object-cover rounded-xl mb-2">
                        @else
                            <div class="w-full h-24 rounded-xl bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-2 text-gray-300">
                                <x-heroicon-o-shopping-bag class="w-8 h-8" />
                            </div>
                        @endif

                        <div class="text-sm font-semibold truncate">{{ $produk->nama }}</div>
                        <div class="text-xs text-gray-500">Rp {{ number_format($produk->harga, 0, ',', '.') }}</div>

                        @if ($produk->stok !== null)
                            <div class="text-[11px] text-gray-400 mt-0.5">Stok: {{ $produk->stok }}</div>
                        @endif

                    </button>

                @empty

                    <div class="col-span-full text-center text-sm text-gray-400 py-10">
                        Belum ada produk aktif untuk lembaga ini.
                    </div>

                @endforelse

            </div>

        </div>

        {{-- KERANJANG --}}
        <div>

            <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-4 sticky top-4">

                <div class="font-semibold mb-3">Keranjang</div>

                <div class="space-y-2 mb-4 max-h-64 overflow-y-auto">

                    @forelse ($cart as $item)

                        <div class="flex items-center justify-between text-sm">

                            <div class="flex-1 truncate">
                                {{ $item['nama'] }}
                                <div class="text-xs text-gray-400">Rp {{ number_format($item['harga'], 0, ',', '.') }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="kurangiKeranjang({{ $item['id'] }})" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/10">-</button>
                                <span class="w-5 text-center">{{ $item['qty'] }}</span>
                                <button type="button" wire:click="tambahKeKeranjang({{ $item['id'] }})" class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/10">+</button>
                            </div>

                        </div>

                    @empty

                        <div class="text-sm text-gray-400 text-center py-6">Belum ada item.</div>

                    @endforelse

                </div>

                <div class="flex items-center justify-between font-semibold border-t border-gray-100 dark:border-white/10 pt-3 mb-4">
                    <span>Total</span>
                    <span>Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                </div>

                <div class="mb-3">
                    <label class="text-xs text-gray-500">Metode Bayar</label>
                    <select wire:model.live="metode" class="w-full rounded-xl border-gray-300 dark:bg-gray-800 text-sm mt-1">
                        <option value="wallet">Wallet Siswa</option>
                        <option value="tunai">Tunai</option>
                    </select>
                </div>

                @if ($metode === 'wallet')
                    <div class="mb-4">
                        <label class="text-xs text-gray-500">Siswa</label>
                        <select wire:model="siswa_id" class="w-full rounded-xl border-gray-300 dark:bg-gray-800 text-sm mt-1">
                            <option value="">-- Cari lewat kolom cari produk / pilih --</option>
                            @foreach ($this->siswaOptions as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_lengkap }} - {{ $s->kelas->nama ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <button
                    type="button"
                    wire:click="checkout"
                    class="w-full rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 text-sm">
                    Bayar
                </button>

            </div>

        </div>

    </div>

</x-filament-panels::page>
