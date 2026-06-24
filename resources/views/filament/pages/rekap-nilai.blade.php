<x-filament-panels::page>

    {{-- FILTER --}}
    {{ $this->form }}

    {{-- BUTTON --}}
    <div class="mt-4 flex justify-end">

        <x-filament::button
            size="lg"
            wire:click="tampilkan"
        >
            Tampilkan Rekap
        </x-filament::button>

    </div>

    {{-- TABLE --}}
    @if (count($rekap))

        <div
        class="
            mt-4
            overflow-hidden
            rounded-xl
            border
            border-gray-200
            bg-white
            shadow-sm
        "
    >

            <div class="overflow-x-auto">

                <table class="w-full">

    {{-- HEADER --}}
    <thead class="bg-gray-50">

        <tr>

            {{-- MAPEL --}}
            <th
                class="
                    rounded-tl-2xl
                    border-b
                    border-gray-200
                    px-6
                    py-3
                    text-left
                    text-sm
                    font-semibold
                    text-gray-800
                "
            >
                Mata Pelajaran
            </th>

            {{-- GURU --}}
            <th
                class="
                    border-b
                    border-l
                    border-gray-200
                    px-6
                    py-3
                    text-left
                    text-sm
                    font-semibold
                    text-gray-800
                "
            >
                Guru Pengampu
            </th>

            {{-- TUGAS --}}
            <th
                class="
                    border-b
                    border-l
                    border-gray-200
                    px-4
                    py-3
                    text-center
                    text-sm
                    font-semibold
                    text-gray-800
                "
            >
                Tugas
            </th>

            {{-- HARIAN --}}
            <th
                class="
                    border-b
                    border-l
                    border-gray-200
                    px-4
                    py-3
                    text-center
                    text-sm
                    font-semibold
                    text-gray-800
                "
            >
                Harian
            </th>

            {{-- UTS --}}
            <th
                class="
                    border-b
                    border-l
                    border-gray-200
                    px-4
                    py-3
                    text-center
                    text-sm
                    font-semibold
                    text-gray-800
                "
            >
                UTS
            </th>

            {{-- UAS --}}
            <th
                class="
                    border-b
                    border-l
                    border-gray-200
                    px-4
                    py-3
                    text-center
                    text-sm
                    font-semibold
                    text-gray-800
                "
            >
                UAS
            </th>

            {{-- DETAIL --}}
            <th
                class="
                    rounded-tr-2xl
                    border-b
                    border-l
                    border-gray-200
                    px-4
                    py-3
                    text-center
                    text-sm
                    font-semibold
                    text-primary-600
                "
            >
                Detail
            </th>

        </tr>

    </thead>

    {{-- BODY --}}
    <tbody class="bg-white">

        @foreach ($rekap as $item)

            <tr class="transition duration-200 hover:bg-gray-50">

                {{-- MAPEL --}}
                <td
                    class="
                        border-t
                        border-gray-100
                        px-6
                        py-3
                        text-sm
                        font-medium
                        text-gray-800

                        {{ $loop->last ? 'rounded-bl-2xl' : '' }}
                    "
                >
                    {{ $item['mapel'] }}
                </td>

                {{-- GURU --}}
                <td
                    class="
                        border-t
                        border-l
                        border-gray-100
                        px-6
                        py-3
                        text-sm
                        text-gray-700
                    "
                >
                    {{ $item['guru'] }}
                </td>

                {{-- TUGAS --}}
                <td
                    class="
                        border-t
                        border-l
                        border-gray-100
                        px-4
                        py-3
                        text-center
                    "
                >
                    @if ($item['tugas'] == 'Selesai')

                        <x-filament::badge color="success">
                            Selesai
                        </x-filament::badge>

                    @else

                        <x-filament::badge color="danger">
                            Belum
                        </x-filament::badge>

                    @endif

                </td>

                {{-- HARIAN --}}
                <td
                    class="
                        border-t
                        border-l
                        border-gray-100
                        px-4
                        py-3
                        text-center
                    "
                >

                    @if ($item['harian'] == 'Selesai')

                        <x-filament::badge color="success">
                            Selesai
                        </x-filament::badge>

                    @else

                        <x-filament::badge color="danger">
                            Belum
                        </x-filament::badge>

                    @endif

                </td>

                {{-- UTS --}}
                <td
                    class="
                        border-t
                        border-l
                        border-gray-100
                        px-4
                        py-3
                        text-center
                    "
                >

                    @if ($item['uts'] == 'Selesai')

                        <x-filament::badge color="success">
                            Selesai
                        </x-filament::badge>

                    @else

                        <x-filament::badge color="danger">
                            Belum
                        </x-filament::badge>

                    @endif

                </td>

                {{-- UAS --}}
                <td
                    class="
                        border-t
                        border-l
                        border-gray-100
                        px-4
                        py-3
                        text-center
                    "
                >

                    @if ($item['uas'] == 'Selesai')

                    <x-filament::badge color="success">
                        Selesai
                    </x-filament::badge>

                @else

                    <x-filament::badge color="danger">
                        Belum
                    </x-filament::badge>

                @endif

                </td>

                {{-- DETAIL --}}
                <td
                    class="
                        border-t
                        border-l
                        border-gray-100
                        px-4
                        py-3
                        text-center

                        {{ $loop->last ? 'rounded-br-2xl' : '' }}
                    "
                >

                    <a
                        href="{{
                            \App\Filament\Pages\DetailNilai::getUrl([
                                'kelas' => $this->data['kelas_id'],
                                'mapel' => $item['mapel_id'],
                                'tahun' => $this->data['tahun_ajaran_id'],
                            ])
                        }}"
                    >

                        <x-filament::button
                            size="xs"
                            color="primary"
                            icon="heroicon-m-eye"
                        >
                            Detail
                        </x-filament::button>

                    </a>

                </td>

            </tr>

        @endforeach

    </tbody>

</table>

            </div>

        </div>

    @endif

</x-filament-panels::page>