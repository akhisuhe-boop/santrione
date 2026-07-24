<x-filament-panels::page>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

        <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="text-sm text-gray-500">Pemasukan Hari Ini</div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">
                Rp {{ number_format($data['pemasukan_hari_ini'], 0, ',', '.') }}
            </div>
            <div class="text-xs text-gray-400 mt-1">{{ $data['transaksi_hari_ini'] }} transaksi</div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="text-sm text-gray-500">Pemasukan Bulan Ini</div>
            <div class="text-2xl font-bold text-teal-600 mt-1">
                Rp {{ number_format($data['pemasukan_bulan_ini'], 0, ',', '.') }}
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="text-sm text-gray-500">Produk Terlaris (Bulan Ini)</div>
            <div class="text-lg font-bold text-gray-800 mt-1">
                {{ $data['produk_terlaris']->first()->nama_produk ?? '-' }}
            </div>
            <div class="text-xs text-gray-400 mt-1">
                {{ $data['produk_terlaris']->first()->total_qty ?? 0 }} terjual
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">

        {{-- PRODUK TERLARIS --}}
        <div class="rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 font-semibold">
                Produk Terlaris Bulan Ini
            </div>

            <div class="divide-y divide-gray-100 dark:divide-white/5">

                @forelse ($data['produk_terlaris'] as $i => $produk)

                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center text-xs font-semibold">
                                {{ $i + 1 }}
                            </span>
                            <span>{{ $produk->nama_produk }}</span>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">{{ $produk->total_qty }} terjual</div>
                            <div class="text-xs text-gray-400">Rp {{ number_format($produk->total_omzet, 0, ',', '.') }}</div>
                        </div>
                    </div>

                @empty

                    <div class="px-5 py-6 text-sm text-gray-400 text-center">Belum ada penjualan bulan ini.</div>

                @endforelse

            </div>

        </div>

        {{-- TRANSAKSI TERBARU --}}
        <div class="rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">

            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 font-semibold">
                Transaksi Terbaru
            </div>

            <div class="divide-y divide-gray-100 dark:divide-white/5">

                @forelse ($data['transaksi_terbaru'] as $trx)

                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <div>
                            <div class="font-medium">{{ $trx->siswa->nama_lengkap ?? 'Umum' }}</div>
                            <div class="text-xs text-gray-400">{{ $trx->tanggal->locale('id')->translatedFormat('d M Y H:i') }} &middot; {{ $trx->items->count() }} item</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">Rp {{ number_format($trx->total, 0, ',', '.') }}</div>
                            <div class="text-xs text-gray-400">{{ ucfirst($trx->metode) }}</div>
                        </div>
                    </div>

                @empty

                    <div class="px-5 py-6 text-sm text-gray-400 text-center">Belum ada transaksi.</div>

                @endforelse

            </div>

        </div>

    </div>

</x-filament-panels::page>
