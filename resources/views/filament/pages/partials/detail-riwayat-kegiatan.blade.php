<div class="max-h-[28rem] overflow-y-auto">
    @if ($riwayat->isEmpty())
        <p class="text-sm text-gray-400 text-center py-6">Belum ada riwayat pada periode/filter ini.</p>
    @else
        <table class="w-full text-sm">
            <thead class="sticky top-0 bg-white dark:bg-gray-900">
                <tr class="text-left text-gray-500 border-b border-gray-100 dark:border-gray-800">
                    <th class="py-2 pr-3">Kegiatan</th>
                    <th class="py-2 pr-3">Tanggal</th>
                    <th class="py-2 pr-3">Status</th>
                    <th class="py-2">Jam Scan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($riwayat as $item)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50">
                        <td class="py-2 pr-3">{{ $item->jadwalKegiatan?->template?->nama_kegiatan ?? '-' }}</td>
                        <td class="py-2 pr-3">{{ $item->jadwalKegiatan?->tanggal ? \Carbon\Carbon::parse($item->jadwalKegiatan->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td class="py-2 pr-3">
                            <span @class([
                                'px-2 py-0.5 rounded-full text-xs font-semibold',
                                'bg-success-50 text-success-700' => $item->status === 'Hadir',
                                'bg-warning-50 text-warning-700' => $item->status === 'Terlambat',
                                'bg-info-50 text-info-700' => $item->status === 'Izin',
                                'bg-gray-100 text-gray-600' => $item->status === 'Sakit',
                                'bg-danger-50 text-danger-700' => ! in_array($item->status, ['Hadir', 'Terlambat', 'Izin', 'Sakit']),
                            ])>{{ $item->status }}</span>
                        </td>
                        <td class="py-2">{{ $item->jam_scan ? \Carbon\Carbon::parse($item->jam_scan)->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
