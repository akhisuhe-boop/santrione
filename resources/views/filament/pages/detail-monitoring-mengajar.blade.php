<x-filament::page>

    <div class="space-y-6">
        {{ $this->form }}   
        {{-- INFO GURU --}}
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">

            <div class="flex items-center justify-between">

                <div>

                    <div class="text-sm text-gray-500">
                        Guru
                    </div>

                    <div class="text-2xl font-bold text-gray-900">
                        {{ $this->record->nama }}
                    </div>

                </div>

            </div>

        </div>

        {{-- TABLE --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="w-full divide-y divide-gray-200">

                    {{-- HEADER --}}
                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                Hari
                            </th>

                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                Tanggal
                            </th>

                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                Mapel
                            </th>

                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                                Kelas
                            </th>

                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                Jam Ke
                            </th>

                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                JP
                            </th>

                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">
                                Status
                            </th>

                        </tr>

                    </thead>

                    {{-- BODY --}}
                    <tbody class="divide-y divide-gray-100 bg-white">
                    @php
                        $jadwals = \App\Models\JadwalPelajaran::query()
                            ->where('pegawai_id', $this->record->id)
                            ->orderByRaw("
                                CASE hari
                                    WHEN 'senin' THEN 1
                                    WHEN 'selasa' THEN 2
                                    WHEN 'rabu' THEN 3
                                    WHEN 'kamis' THEN 4
                                    WHEN 'jumat' THEN 5
                                    WHEN 'sabtu' THEN 6
                                    WHEN 'minggu' THEN 7
                                END
                            ")
                            ->orderBy('jam_ke')
                            ->get();

                            $mapHari = [
                            'senin' => 1,
                            'selasa' => 2,
                            'rabu' => 3,
                            'kamis' => 4,
                            'jumat' => 5,
                            'sabtu' => 6,
                            'minggu' => 7,

                        ];
                        $periode = \Carbon\CarbonPeriod::create(
                            $this->tanggalMulai,
                            $this->tanggalSelesai
                        );
                    @endphp
                    @foreach($jadwals as $jadwal)
                        @foreach($periode as $tanggal)
                            @php

                                $hariTanggal =
                                    strtolower($tanggal->locale('id')->dayName);

                            @endphp

                            @if($hariTanggal === strtolower($jadwal->hari))
                                @php
                                    $jurnal = \App\Models\JurnalMengajar::query()
                                        ->where('pegawai_id', $this->record->id)
                                        ->where('kelas_id', $jadwal->kelas_id)
                                        ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
                                        ->where('jam_ke', $jadwal->jam_ke)
                                        ->whereDate('tanggal', $tanggal)
                                        ->where('status', 'valid')
                                        ->first();
                                @endphp

                                <tr class="hover:bg-gray-50 transition">

                                    {{-- HARI --}}
                                    <td class="px-3 py-2 text-sm font-medium">

                                        {{ ucfirst($jadwal->hari) }}

                                    </td>

                                    {{-- TANGGAL --}}
                                    <td class="px-3 py-2 text-sm">
                                        @if($jurnal)
                                            {{ $tanggal->translatedFormat('d M Y') }}
                                        @else
                                            <span class="text-danger-600">
                                                {{ $tanggal->translatedFormat('d M Y') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- MAPEL --}}
                                    <td class="px-3 py-2 text-sm">
                                        {{ $jadwal->mataPelajaran->nama }}
                                    </td>

                                    {{-- KELAS --}}
                                    <td class="px-3 py-2 text-sm">
                                        {{ $jadwal->kelas->nama }}
                                    </td>

                                    {{-- JAM KE --}}
                                    <td class="px-3 py-2 text-center text-sm">
                                        {{ $jadwal->jam_ke }}
                                    </td>

                                    {{-- JP --}}
                                    <td class="px-3 py-2 text-center text-sm">
                                        {{ $jadwal->durasi_jam }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="px-3 py-2 text-center">
                                        @if($jurnal)
                                            <x-filament::badge color="success">
                                                Mengajar
                                            </x-filament::badge>
                                        @else
                                            <x-filament::badge color="danger">
                                                Tidak Mengajar
                                            </x-filament::badge>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-filament::page>