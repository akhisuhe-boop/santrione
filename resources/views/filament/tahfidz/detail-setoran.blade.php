<div class="p-4 space-y-3">

    <table class="w-full text-sm border rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Tanggal</th>
                <th class="p-2 text-left">Juz</th>
                <th class="p-2 text-left">Surah</th>
                <th class="p-2 text-left">Ayat</th>
                <th class="p-2 text-left">Jenis</th>
                <th class="p-2 text-left">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $d)
                <tr class="border-t">
                    <td class="p-2">{{ \Carbon\Carbon::parse($d->tanggal)->format('d/m/Y') }}</td>
                    <td class="p-2">{{ $d->juz->nama ?? '-' }}</td>
                    <td>{{ $d->surah?->nama ?? '-' }}</td>
                    <td class="p-2">{{ $d->ayat_dari }} - {{ $d->ayat_sampai }}</td>
                    <td class="p-2 capitalize">{{ $d->jenis }}</td>
                    <td class="p-2">{{ $d->nilai }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-3 text-center text-gray-500">
                        Belum ada setoran
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>