<table class="w-full">

    <thead class="border-b border-gray-200 dark:border-white/10">

        <tr>

            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                Komponen
            </th>

            <th class="w-20 px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                Qty
            </th>

            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">
                Tarif
            </th>

            <th class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">
                Subtotal
            </th>

        </tr>

    </thead>

    <tbody class="divide-y divide-gray-100 dark:divide-white/5">

        @foreach($getRecord()->items as $item)

            <tr class="transition hover:bg-gray-50 dark:hover:bg-white/[0.03]">

                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">
                    {{ $item->nama_komponen }}
                </td>

                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ $item->qty }}
                </td>

                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                    Rp {{ number_format($item->tarif,0,',','.') }}
                </td>

                <td class="px-4 py-2 text-right whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($item->subtotal,0,',','.') }}
                </td>

            </tr>

            {{-- ================================================= --}}
            {{-- RINCIAN JURNAL MENGAJAR (materi) untuk komponen    --}}
            {{-- honor per JP, supaya "ngelink" ke jurnal mengajar  --}}
            {{-- yang jadi dasar perhitungan honor ini.             --}}
            {{-- ================================================= --}}

            @if (str_starts_with($item->nama_komponen, 'Honor') && $item->pegawai_lembaga_id)

                @php
                    $payroll = $getRecord();

                    $isPengganti = str_contains($item->nama_komponen, 'Pengganti');

                    $jurnalList = \App\Models\JurnalMengajar::query()
                        ->with(['kelas', 'mataPelajaran', 'jamPelajaran'])
                        ->where('pegawai_lembaga_id', $item->pegawai_lembaga_id)
                        ->whereMonth('tanggal', $payroll->bulan)
                        ->whereYear('tanggal', $payroll->tahun)
                        ->when(
                            $isPengganti,
                            fn ($q) => $q->whereNotNull('pegawai_asli_id'),
                            fn ($q) => $q->whereNull('pegawai_asli_id'),
                        )
                        ->orderBy('tanggal')
                        ->get();
                @endphp

                @if ($jurnalList->isNotEmpty())

                    <tr>
                        <td colspan="4" class="bg-gray-50 px-4 py-3 dark:bg-white/[0.02]">

                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Rincian Jurnal Mengajar
                            </div>

                            <div class="mt-2 space-y-1.5">

                                @foreach ($jurnalList as $jurnal)

                                    <div class="flex items-start justify-between gap-3 text-xs text-gray-600 dark:text-gray-300">

                                        <div class="shrink-0 font-medium text-gray-800 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($jurnal->tanggal)->locale('id')->translatedFormat('d M Y') }}
                                        </div>

                                        <div class="flex-1">
                                            {{ $jurnal->kelas->nama ?? '-' }} • {{ $jurnal->mataPelajaran->nama ?? '-' }}
                                            ({{ $jurnal->jamPelajaran->durasi_jp ?? '-' }} JP)
                                            @if (filled($jurnal->materi))
                                                — {{ \Illuminate\Support\Str::limit($jurnal->materi, 60) }}
                                            @else
                                                <span class="italic text-amber-600">— materi belum diisi</span>
                                            @endif
                                        </div>

                                    </div>

                                @endforeach

                            </div>

                        </td>
                    </tr>

                @endif

            @endif

        @endforeach

    </tbody>

</table>