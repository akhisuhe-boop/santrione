<x-filament-panels::page>

    <x-filament::section heading="Rentang Tanggal" icon="heroicon-o-calendar">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-xs font-medium text-gray-500">Dari Tanggal</label>
                <input
                    type="date"
                    wire:model.live="dari"
                    class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                />
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Sampai Tanggal</label>
                <input
                    type="date"
                    wire:model.live="sampai"
                    class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm"
                />
            </div>
        </div>
    </x-filament::section>

    @php
        $laporan = $this->getLaporan();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-filament::section>
            <div class="text-sm text-gray-500">Total Pemasukan</div>
            <div class="text-2xl font-bold text-success-600">Rp {{ number_format($laporan['total_masuk'], 0, ',', '.') }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Total Pengeluaran</div>
            <div class="text-2xl font-bold text-danger-600">Rp {{ number_format($laporan['total_keluar'], 0, ',', '.') }}</div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-sm text-gray-500">Laba / Rugi Bersih</div>
            <div class="text-2xl font-bold {{ $laporan['laba_bersih'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                Rp {{ number_format($laporan['laba_bersih'], 0, ',', '.') }}
            </div>
        </x-filament::section>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-filament::section heading="Rincian Pemasukan per Kategori" icon="heroicon-o-arrow-trending-up">
            @if (empty($laporan['rincian_masuk']))
                <div class="text-center py-6 text-gray-400">Belum ada pemasukan di rentang ini.</div>
            @else
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($laporan['rincian_masuk'] as $row)
                            <tr class="border-b border-gray-50 dark:border-gray-800">
                                <td class="py-2">{{ $row['kategori'] }}</td>
                                <td class="py-2 text-right font-medium">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                            <td class="py-2 font-semibold">Total</td>
                            <td class="py-2 text-right font-semibold text-success-600">Rp {{ number_format($laporan['total_masuk'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </x-filament::section>

        <x-filament::section heading="Rincian Pengeluaran per Kategori" icon="heroicon-o-arrow-trending-down">
            @if (empty($laporan['rincian_keluar']))
                <div class="text-center py-6 text-gray-400">Belum ada pengeluaran di rentang ini.</div>
            @else
                <table class="w-full text-sm">
                    <tbody>
                        @foreach ($laporan['rincian_keluar'] as $row)
                            <tr class="border-b border-gray-50 dark:border-gray-800">
                                <td class="py-2">{{ $row['kategori'] }}</td>
                                <td class="py-2 text-right font-medium">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-200 dark:border-gray-700">
                            <td class="py-2 font-semibold">Total</td>
                            <td class="py-2 text-right font-semibold text-danger-600">Rp {{ number_format($laporan['total_keluar'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </x-filament::section>
    </div>

</x-filament-panels::page>
