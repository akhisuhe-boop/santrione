<x-filament-panels::page>

    @php
        $laporan = $this->getLaporan();
        $totalKeseluruhan = $this->getTotalKeseluruhan();
    @endphp

    <x-filament::section>
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm text-gray-500">Total Pendapatan Riil (semua bulan)</div>
                <div class="text-3xl font-bold text-success-600">Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</div>
            </div>
            <div class="text-xs text-gray-400 max-w-xs text-right">
                Hanya menghitung pembayaran yang BERHASIL dikonfirmasi (Duitku/Midtrans/Xendit/verifikasi manual) — bukan proyeksi/estimasi.
            </div>
        </div>
    </x-filament::section>

    @forelse ($laporan as $bulan => $data)
        <x-filament::section :heading="\Carbon\Carbon::createFromFormat('Y-m', $bulan)->locale('id')->translatedFormat('F Y')">

            <div class="overflow-x-auto -mx-2">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase text-gray-400 border-b border-gray-100 dark:border-gray-700">
                            <th class="px-2 py-2 font-semibold">Yayasan</th>
                            <th class="px-2 py-2 font-semibold text-center">Jumlah Transaksi</th>
                            <th class="px-2 py-2 font-semibold text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['yayasan'] as $y)
                            <tr class="border-b border-gray-50 dark:border-gray-800">
                                <td class="px-2 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $y['yayasan_nama'] }}</td>
                                <td class="px-2 py-3 text-center text-gray-600 dark:text-gray-300">{{ $y['jumlah_transaksi'] }}</td>
                                <td class="px-2 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($y['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="px-2 py-3 font-bold text-gray-900 dark:text-white">TOTAL BULAN INI</td>
                            <td class="px-2 py-3 text-right font-bold text-success-600">Rp {{ number_format($data['total_bulan'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </x-filament::section>
    @empty
        <x-filament::section>
            <div class="text-center py-8 text-gray-400">
                Belum ada pembayaran yang berhasil tercatat.
            </div>
        </x-filament::section>
    @endforelse

</x-filament-panels::page>
