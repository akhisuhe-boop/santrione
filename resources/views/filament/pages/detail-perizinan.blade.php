<div class="fi-ta-content relative overflow-x-auto rounded-xl border">

    <table class="fi-ta-table w-full text-sm text-left">

        <thead class="fi-ta-header bg-gray-50 dark:bg-white/5">
            <tr>
                <th class="px-3 py-2">Keluar</th>
                <th class="px-3 py-2">Batas Kembali</th>
                <th class="px-3 py-2">Kembali Real</th>
                <th class="px-3 py-2 text-center">Hari</th>
                <th class="px-3 py-2">Keperluan</th>
                <th class="px-3 py-2">Penjemput</th>
                <th class="px-3 py-2">Status</th>
                <th class="px-3 py-2">Terlambat</th>
            </tr>
        </thead>

        <tbody class="divide-y">
            @foreach($data as $d)
                @php
                    $keluar = $d->waktu_keluar ? \Carbon\Carbon::parse($d->waktu_keluar) : null;
                    $batas = $d->tanggal_selesai ? \Carbon\Carbon::parse($d->tanggal_selesai) : null;
                    $kembali = $d->waktu_kembali ? \Carbon\Carbon::parse($d->waktu_kembali) : null;

                    // 🔥 FIX: HITUNG HARI TANPA JAM (ANTI DESIMAL)
                    $hari = ($d->tanggal_mulai && $d->tanggal_selesai)
                        ? \Carbon\Carbon::parse($d->tanggal_mulai)->startOfDay()
                            ->diffInDays(\Carbon\Carbon::parse($d->tanggal_selesai)->startOfDay()) + 1
                        : null;

                    $terlambat = $d->keterangan_waktu !== 'tepat_waktu' && $d->keterangan_waktu != null;
                @endphp

                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                    
                    <td class="px-3 py-2">
                        {{ $keluar ? $keluar->format('d-m-Y H:i') : '-' }}
                    </td>

                    <td class="px-3 py-2">
                        {{ $batas ? $batas->format('d-m-Y H:i') : '-' }}
                    </td>

                    <td class="px-3 py-2">
                        {{ $kembali ? $kembali->format('d-m-Y H:i') : '-' }}
                    </td>

                    <td class="px-3 py-2 text-center">
                        {{ $hari }}
                    </td>

                    <td class="px-3 py-2">
                        {{ $d->keperluan }}
                    </td>

                    <td class="px-3 py-2">
                        {{ $d->penjemput ?? '-' }}
                        @if($d->hubungan)
                            <div class="text-xs text-gray-500">
                                ({{ $d->hubungan }})
                            </div>
                        @endif
                    </td>

                    <td class="px-3 py-2">
                        @if($d->status == 'selesai')
                            <span class="px-2 py-1 text-xs rounded bg-success-100 text-success-700">
                                SELESAI
                            </span>
                        @elseif($d->status == 'dijemput')
                            <span class="px-2 py-1 text-xs rounded bg-warning-100 text-warning-700">
                                DIJEMPUT
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                {{ strtoupper($d->status) }}
                            </span>
                        @endif
                    </td>

                    <td class="px-3 py-2">
                        @if($terlambat)
                            <span class="px-2 py-1 text-xs rounded bg-danger-100 text-danger-700">
                                YA
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-success-100 text-success-700">
                                TIDAK
                            </span>
                        @endif
                    </td>

                </tr>
            @endforeach
        </tbody>

    </table>

</div>