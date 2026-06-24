<x-filament-panels::page>

    {{-- HEADER INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- CARD --}}
        <div
            style="
                border-radius:16px;
                background:white;
                border:1px solid #e5e7eb;
            "
            class="p-6 shadow-sm"
        >
            <div class="text-sm text-gray-500 font-medium">
                Mata Pelajaran
            </div>

            <div class="mt-1 text-xl font-bold text-gray-950">
                {{ $this->mapel->nama }}
            </div>
        </div>

        {{-- CARD --}}
        <div
            style="
                border-radius:16px;
                background:white;
                border:1px solid #e5e7eb;
            "
            class="p-6 shadow-sm"
        >
            <div class="text-sm text-gray-500 font-medium">
                Kelas
            </div>

            <div class="mt-1 text-xl font-bold text-gray-950">
                {{ $this->kelas->nama }}
            </div>
        </div>

        {{-- CARD --}}
        <div
            style="
                border-radius:16px;
                background:white;
                border:1px solid #e5e7eb;
            "
            class="p-6 shadow-sm"
        >
            <div class="text-sm text-gray-500 font-medium">
                Guru Pengampu
            </div>

            <div class="mt-1 text-xl font-bold leading-snug text-gray-950">
                {{ $this->guru }}
            </div>
        </div>

        {{-- CARD --}}
        <div
            style="
                border-radius:16px;
                background:white;
                border:1px solid #e5e7eb;
            "
            class="p-6 shadow-sm"
        >
            <div class="text-sm text-gray-500 font-medium">
                Tahun Ajaran
            </div>

            <div class="mt-1 text-xl font-bold text-gray-950">
                {{ $this->tahunAjaran->nama }}
                -
                {{ ucfirst($this->tahunAjaran->semester) }}
            </div>
        </div>

    </div>

    @php

        $avgTugas = collect($siswas)
            ->whereNotNull('tugas')
            ->avg('tugas');

        $avgHarian = collect($siswas)
            ->whereNotNull('harian')
            ->avg('harian');

        $avgUts = collect($siswas)
            ->whereNotNull('uts')
            ->avg('uts');

        $avgUas = collect($siswas)
            ->whereNotNull('uas')
            ->avg('uas');

        $avgNilaiAkhir = collect($siswas)
            ->whereNotNull('nilai_akhir')
            ->avg('nilai_akhir');

        $desc = 'D';

        if ($avgNilaiAkhir >= 95) {

            $desc = 'A+';

        } elseif ($avgNilaiAkhir >= 90) {

            $desc = 'A';

        } elseif ($avgNilaiAkhir >= 85) {

            $desc = 'A-';

        } elseif ($avgNilaiAkhir >= 80) {

            $desc = 'B+';

        } elseif ($avgNilaiAkhir >= 75) {

            $desc = 'B';

        } elseif ($avgNilaiAkhir >= 70) {

            $desc = 'B-';

        } elseif ($avgNilaiAkhir >= 65) {

            $desc = 'C+';

        } elseif ($avgNilaiAkhir >= 60) {

            $desc = 'C';

        }

    @endphp

    {{-- TABLE --}}
    <div
        style="
            border-radius: 24px;
            overflow: hidden;
        "
        class="
            mt-5
            border
            border-gray-200
            bg-white
            shadow-sm
        "
    >

        <div
            style="
                border-radius: 24px;
                overflow: hidden;
            "
            class="overflow-x-auto"
        >

            <table
                class="
                    w-full
                    border-separate
                    border-spacing-0
                "
            >

                {{-- HEADER --}}
                <thead class="bg-gray-50">

                    <tr>

                        {{-- NAMA --}}
                        <th
                            class="
                                rounded-tl-[24px]
                                border-b
                                border-gray-200
                                px-6
                                py-3
                                text-left
                                text-sm
                                font-bold
                                text-gray-900
                            "
                        >
                            Nama Siswa
                        </th>

                        {{-- TUGAS --}}
                        <th class="border-b border-gray-200 px-4 py-3 text-center">

                            <div class="text-sm font-bold text-gray-900">
                                Tugas
                            </div>

                            <div
                                style="
                                    font-size:12px;
                                    line-height:10px;
                                    font-weight:400;
                                    color:#9ca3af;
                                    margin-top:5px;
                                "
                            >
                                20%
                            </div>

                        </th>

                        {{-- HARIAN --}}
                        <th class="border-b border-gray-200 px-4 py-3 text-center">

                            <div class="text-sm font-bold text-gray-900">
                                Harian
                            </div>

                            <div
                                style="
                                    font-size:12px;
                                    line-height:10px;
                                    font-weight:400;
                                    color:#9ca3af;
                                    margin-top:5px;
                                "
                            >
                                30%
                            </div>

                        </th>

                        {{-- UTS --}}
                        <th class="border-b border-gray-200 px-4 py-3 text-center">

                            <div class="text-sm font-bold text-gray-900">
                                UTS
                            </div>

                            <div
                                style="
                                    font-size:12px;
                                    line-height:10px;
                                    font-weight:400;
                                    color:#9ca3af;
                                    margin-top:5px;
                                "
                            >
                                20%
                            </div>

                        </th>

                        {{-- UAS --}}
                        <th class="border-b border-gray-200 px-4 py-3 text-center">

                            <div class="text-sm font-bold text-gray-900">
                                UAS
                            </div>

                            <div
                                style="
                                    font-size:12px;
                                    line-height:10px;
                                    font-weight:400;
                                    color:#9ca3af;
                                    margin-top:5px;
                                "
                            >
                                30%
                            </div>

                        </th>

                        {{-- NILAI AKHIR --}}
                        <th
                            class="
                                border-b
                                border-gray-200
                                px-4
                                py-3
                                text-center
                                text-sm
                                font-bold
                            "
                        >
                            Nilai Akhir
                        </th>

                        {{-- GRADE --}}
                        <th
                            class="
                                border-b
                                border-gray-200
                                px-4
                                py-3
                                text-center
                                text-sm
                                font-bold
                            "
                        >
                            Grade
                        </th>

                        {{-- STATUS --}}
                        <th
                            class="
                                rounded-tr-[24px]
                                border-b
                                border-gray-200
                                px-4
                                py-3
                                text-center
                                text-sm
                                font-bold
                            "
                        >
                            Status
                        </th>

                    </tr>

                </thead>

                {{-- BODY --}}
                <tbody class="bg-white divide-y divide-gray-100">

                    @foreach ($siswas as $siswa)

                        <tr
                            class="
                                transition
                                {{
                                    $siswa['status'] == 'Belum Lengkap'
                                        ? 'bg-danger-50/30'
                                        : 'hover:bg-gray-50'
                                }}
                            "
                        >

                            {{-- NAMA --}}
                            <td class="px-6 py-2.5 text-sm">
                                {{ $siswa['nama'] }}
                            </td>

                            {{-- TUGAS --}}
                            <td class="px-3 py-2.5 text-center text-sm">
                                {{ $siswa['tugas'] ?? '-' }}
                            </td>

                            {{-- HARIAN --}}
                            <td class="px-3 py-2.5 text-center text-sm">
                                {{ $siswa['harian'] ?? '-' }}
                            </td>

                            {{-- UTS --}}
                            <td class="px-3 py-2.5 text-center text-sm">
                                {{ $siswa['uts'] ?? '-' }}
                            </td>

                            {{-- UAS --}}
                            <td class="px-3 py-2.5 text-center text-sm">
                                {{ $siswa['uas'] ?? '-' }}
                            </td>

                            {{-- NILAI AKHIR --}}
                            <td
                                class="
                                    px-3
                                    py-2.5
                                    text-center
                                    text-sm
                                    font-bold
                                "
                            >
                                {{ round($siswa['nilai_akhir']) }}
                            </td>

                            {{-- GRADE --}}
                            <td
                                class="
                                    px-3
                                    py-2.5
                                    text-center
                                    text-sm
                                    font-bold
                                "
                            >

                                @if (
                                    in_array(
                                        $siswa['grade'],
                                        ['A+', 'A', 'A-']
                                    )
                                )

                                    <span
                                        title="{{ $siswa['deskripsi'] }}"
                                        class="text-success-600 cursor-help"
                                    >
                                        {{ $siswa['grade'] }}
                                    </span>

                                @elseif (
                                    in_array(
                                        $siswa['grade'],
                                        ['B+', 'B', 'B-']
                                    )
                                )

                                    <span
                                        title="{{ $siswa['deskripsi'] }}"
                                        class="text-primary-600 cursor-help"
                                    >
                                        {{ $siswa['grade'] }}
                                    </span>

                                @elseif (
                                    in_array(
                                        $siswa['grade'],
                                        ['C+', 'C', 'C-']
                                    )
                                )

                                    <span
                                        title="{{ $siswa['deskripsi'] }}"
                                        class="text-warning-600 cursor-help"
                                    >
                                        {{ $siswa['grade'] }}
                                    </span>

                                @elseif (
                                    in_array(
                                        $siswa['grade'],
                                        ['D', 'E']
                                    )
                                )

                                    <span
                                        title="{{ $siswa['deskripsi'] }}"
                                        class="text-danger-600 cursor-help"
                                    >
                                        {{ $siswa['grade'] }}
                                    </span>

                                @else

                                    -

                                @endif

                            </td>

                            {{-- STATUS --}}
                            <td class="px-4 py-2.5 text-center">

                                @if ($siswa['status'] == 'Lengkap')

                                    <x-filament::badge color="success">
                                        Selesai
                                    </x-filament::badge>

                                @else

                                    <x-filament::badge color="danger">
                                        Belum Lengkap
                                    </x-filament::badge>

                                @endif

                            </td>

                        </tr>

                    @endforeach

                </tbody>

                {{-- FOOTER --}}
                <tfoot class="bg-gray-50">

                    <tr>

                        <td
                            colspan="8"
                            class="
                                border-t
                                border-gray-200
                                bg-gray-100
                                px-6
                                py-2.5
                                text-sm
                                font-bold
                            "
                        >
                            Summary Nilai Kelas
                        </td>

                    </tr>

                    <tr class="bg-white">

                        {{-- LABEL --}}
                        <td
                            class="
                                rounded-bl-[24px]
                                px-6
                                py-2.5
                                text-base
                                font-bold
                            "
                        >
                            Rata-Rata Kelas
                        </td>

                        {{-- TUGAS --}}
                        <td class="px-3 py-2.5 text-center">
                            <div class="text-xs text-gray-500">
                                Tugas
                            </div>

                            <div class="mt-2 text-base font-bold">
                                {{ round($avgTugas) }}
                            </div>
                        </td>

                        {{-- HARIAN --}}
                        <td class="px-3 py-2.5 text-center">
                            <div class="text-xs text-gray-500">
                                Harian
                            </div>

                            <div class="mt-2 text-base font-bold">
                                {{ round($avgHarian) }}
                            </div>
                        </td>

                        {{-- UTS --}}
                        <td class="px-3 py-2.5 text-center">
                            <div class="text-xs text-gray-500">
                                UTS
                            </div>

                            <div class="mt-2 text-base font-bold">
                                {{ round($avgUts) }}
                            </div>
                        </td>

                        {{-- UAS --}}
                        <td class="px-3 py-2.5 text-center">
                            <div class="text-xs text-gray-500">
                                UAS
                            </div>

                            <div class="mt-2 text-base font-bold">
                                {{ round($avgUas) }}
                            </div>
                        </td>

                        {{-- NILAI AKHIR --}}
                        <td class="px-3 py-2.5 text-center">
                            <div class="text-xs text-gray-500">
                                Nilai Akhir
                            </div>

                            <div class="mt-2 text-base font-bold">
                                {{ round($avgNilaiAkhir) }}
                            </div>
                        </td>

                        {{-- GRADE --}}
                        <td
                            class="
                                px-3
                                py-2.5
                                text-center
                                text-sm
                                font-bold
                            "
                        >

                            @if (
                                in_array(
                                    $desc,
                                    ['A+', 'A', 'A-']
                                )
                            )

                                <span class="text-success-600">
                                    {{ $desc }}
                                </span>

                            @elseif (
                                in_array(
                                    $desc,
                                    ['B+', 'B', 'B-']
                                )
                            )

                                <span class="text-primary-600">
                                    {{ $desc }}
                                </span>

                            @elseif (
                                in_array(
                                    $desc,
                                    ['C+', 'C', 'C-']
                                )
                            )

                                <span class="text-warning-600">
                                    {{ $desc }}
                                </span>

                            @elseif (
                                in_array(
                                    $desc,
                                    ['D', 'E']
                                )
                            )

                                <span class="text-danger-600">
                                    {{ $desc }}
                                </span>

                            @else

                                -

                            @endif

                        </td>

                        {{-- STATUS --}}
                        <td
                            class="
                                rounded-br-[24px]
                                px-4
                                py-2.5
                                text-center
                            "
                        >

                            <x-filament::badge color="gray">
                                Summary
                            </x-filament::badge>

                        </td>

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

</x-filament-panels::page>