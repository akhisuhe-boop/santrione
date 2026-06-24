<x-filament-panels::page>

    <div class="space-y-6">

        {{-- FILTER --}}
        {{ $this->form }}

        {{-- CONTENT --}}
        @if ($siswa)

            {{-- HEADER --}}
            <div
                style="
                    border-radius:16px;
                    background:white;
                    border:1px solid #e5e7eb;
                "
                class="overflow-hidden shadow-sm"
            >

                {{-- TOP --}}
                <div
                    class="
                        border-b
                        border-gray-100
                        px-6
                        py-5
                    "
                >

                    <div
                        class="
                            flex
                            items-center
                            justify-between
                        "
                    >

                        <div>

                            <h2
                                class="
                                    text-2xl
                                    font-bold
                                    text-gray-900
                                "
                            >
                                Raport Siswa
                            </h2>

                            <p
                                class="
                                    mt-1
                                    text-sm
                                    text-gray-500
                                "
                            >
                                Rekap nilai akademik dan non akademik siswa
                            </p>

                        </div>

                        <x-filament::button
                            color="success"
                            icon="heroicon-m-printer"
                            tag="a"
                            :href="route('raport.pdf', $siswa->id)"
                            target="_blank"
                        >
                            Print Raport
                        </x-filament::button>

                    </div>

                </div>

                {{-- IDENTITAS --}}
                <div class="p-6">

                    <div
                        class="
                            grid
                            grid-cols-1
                            gap-6
                            md:grid-cols-2
                        "
                    >

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Nama Siswa
                            </div>

                            <div
                                class="
                                    mt-2
                                    text-lg
                                    font-black
                                    tracking-tight
                                    text-gray-900
                                "
                            >
                                {{ $siswa->nama_lengkap }}
                            </div>

                        </div>

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Kelas
                            </div>

                            <div
                                class="
                                    mt-2
                                    text-lg
                                    font-black
                                    tracking-tight
                                    text-gray-900
                                "
                            >
                                {{ $siswa->kelas->nama ?? '-' }}
                            </div>

                        </div>

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Semester
                            </div>

                            <div
                                class="
                                    mt-2
                                    text-lg
                                    font-black
                                    tracking-tight
                                    text-gray-900
                                "
                            >
                                {{ ucfirst($tahunAjaran->semester ?? '-') }}
                            </div>

                        </div>

                        <div>

                            <div
                                class="
                                    text-xs
                                    font-medium
                                    uppercase
                                    tracking-wide
                                    text-gray-500
                                "
                            >
                                Tahun Ajaran
                            </div>

                            <div
                                class="
                                    mt-2
                                    text-lg
                                    font-black
                                    tracking-tight
                                    text-gray-900
                                "
                            >
                                {{ $tahunAjaran->nama ?? '-' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

{{-- SUMMARY --}}
<div class="flex gap-4">

    @php
        $cards = [

            [
                'title' => 'Total Nilai',
                'value' => collect($this->nilaiAkademik)
                    ->sum('nilai_akhir'),

                'color' => 'text-primary-600',
            ],

            [
                'title' => 'Rata-Rata',
                'value' => $summary['rata_rata'] ?? 0,

                'color' => 'text-success-600',
            ],

            [
                'title' => 'Nilai Tertinggi',
                'value' => $summary['tertinggi'] ?? 0,

                'color' => 'text-warning-600',
            ],

            [
                'title' => 'Nilai Terendah',
                'value' => $summary['terendah'] ?? 0,

                'color' => 'text-danger-600',
            ],

        ];
    @endphp

    @foreach ($cards as $card)

        <div
            style="
                border-radius:16px;
                background:white;
                border:1px solid #e5e7eb;
            "
            class="flex-1 p-6 shadow-sm"
        >

            <div
                class="
                    text-sm
                    font-medium
                    text-gray-500
                "
            >
                {{ $card['title'] }}
            </div>

            <div
                style="
                    font-size:25px;
                    line-height:1;
                    font-weight:900;
                    letter-spacing:-2px;
                "
                class="
                    mt-2
                    {{ $card['color'] }}
                "
            >
                {{ $card['value'] }}
            </div>

        </div>

    @endforeach

</div>

            {{-- AKADEMIK --}}
            <div
                style="
                    border-radius:16px;
                    background:white;
                    border:1px solid #e5e7eb;
                "
                class="overflow-hidden shadow-sm"
            >

                <div
                    class="
                        border-b
                        border-gray-100
                        px-6
                        py-5
                    "
                >

                    <h3
                        class="
                            text-lg
                            font-bold
                            text-gray-900
                        "
                    >
                        Nilai Akademik
                    </h3>

                </div>

                <div class="overflow-x-auto">

                    <table class="w-full">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                    No
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                    Mata Pelajaran
                                </th>

                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                    Nilai
                                </th>

                                <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                    Grade
                                </th>

                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                    Deskripsi
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-100">

                            @forelse ($nilaiAkademik as $item)

                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="px-4 py-3 text-sm font-bold text-gray-900">
                                        <div class="font-bold">
                                            {{ $item['mapel'] }}
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            Guru :
                                            {{ $item['guru'] }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-xl bg-primary-50 px-3 py-1 text-sm font-bold text-primary-700">
                                            {{ $item['nilai_akhir'] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center rounded-xl bg-success-50 px-3 py-1 text-sm font-bold text-success-700">
                                            {{ $item['grade'] }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-sm leading-7 text-gray-700">
                                        {{ $item['deskripsi'] }}
                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="5"
                                        class="
                                            px-6
                                            py-10
                                            text-center
                                            text-sm
                                            text-gray-500
                                        "
                                    >
                                        Belum ada nilai akademik.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

            {{-- NON AKADEMIK --}}
            <div
                class="
                    grid
                    grid-cols-1
                    gap-6
                    md:grid-cols-2
                "
            >

                {{-- KEPRIBADIAN --}}
                <div
                    style="
                        border-radius:16px;
                        background:white;
                        border:1px solid #e5e7eb;
                    "
                    class="overflow-hidden shadow-sm"
                >

                    <div class="border-b border-gray-100 px-4 py-3">

                        <h3 class="text-lg font-bold text-gray-900">
                            Kepribadian
                        </h3>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                        Aspek
                                    </th>

                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                        Nilai
                                    </th>

                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                        Grade
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @forelse ($nonAkademik?->kepribadians ?? [] as $item)

                                    <tr class="hover:bg-gray-50">

                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $item->aspek }}
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-xl bg-primary-50 px-3 py-1 text-sm font-bold text-primary-700">
                                                {{ $item->nilai }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-xl bg-success-50 px-3 py-1 text-sm font-bold text-success-700">
                                                {{ $item->grade }}
                                            </span>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="
                                                px-6
                                                py-8
                                                text-center
                                                text-sm
                                                text-gray-500
                                            "
                                        >
                                            Belum ada data kepribadian.
                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                {{-- EKSTRAKURIKULER --}}
                <div
                    style="
                        border-radius:16px;
                        background:white;
                        border:1px solid #e5e7eb;
                    "
                    class="overflow-hidden shadow-sm"
                >

                    <div class="border-b border-gray-100 px-4 py-3">

                        <h3 class="text-lg font-bold text-gray-900">
                            Ekstrakurikuler
                        </h3>

                    </div>

                    <div class="overflow-x-auto">

                        <table class="w-full">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                                        Ekskul
                                    </th>

                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                        Nilai
                                    </th>

                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">
                                        Grade
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-gray-100">

                                @forelse ($nonAkademik?->ekstrakurikulers ?? [] as $item)

                                    <tr class="hover:bg-gray-50">

                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $item->nama_ekskul }}
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-xl bg-primary-50 px-3 py-1 text-sm font-bold text-primary-700">
                                                {{ $item->nilai }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center rounded-xl bg-success-50 px-3 py-1 text-sm font-bold text-success-700">
                                                {{ $item->grade }}
                                            </span>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="
                                                px-6
                                                py-8
                                                text-center
                                                text-sm
                                                text-gray-500
                                            "
                                        >
                                            Belum ada data ekstrakurikuler.
                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            {{-- CATATAN --}}
            <div
                style="
                    border-radius:16px;
                    background:white;
                    border:1px solid #e5e7eb;
                "
                class="overflow-hidden shadow-sm"
            >

                <div
                    class="
                        border-b
                        border-gray-100
                        px-4
                        py-3
                    "
                >

                    <h3
                        class="
                            text-lg
                            font-bold
                            text-gray-900
                        "
                    >
                        Catatan Wali Kelas
                    </h3>

                </div>

                <div class="p-6">

                    <div
                        class="
                            rounded-2xl
                            bg-warning-50
                            p-5
                            text-sm
                            leading-7
                            text-gray-700
                        "
                    >
                        {{ $nonAkademik?->catatan_wali_kelas ?: 'Belum ada catatan wali kelas.' }}
                    </div>

                </div>

            </div>

        @endif

    </div>

</x-filament-panels::page>