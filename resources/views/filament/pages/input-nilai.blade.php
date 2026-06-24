<x-filament-panels::page>

    {{ $this->form }}

    {{-- TABLE WRAPPER --}}
    <div
        class="
            fi-ta-ctn
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

            <table class="w-full text-sm">

                {{-- HEADER --}}
                <thead class="bg-gray-50">

                    <tr class="divide-x divide-gray-100">

                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">
                            Nama Siswa
                        </th>

                        <th class="px-4 py-4 text-center text-sm font-semibold text-gray-700">
                            Tugas
                        </th>

                        <th class="px-4 py-4 text-center text-sm font-semibold text-gray-700">
                            Harian
                        </th>

                        <th class="px-4 py-4 text-center text-sm font-semibold text-gray-700">
                            UTS
                        </th>

                        <th class="px-4 py-4 text-center text-sm font-semibold text-gray-700">
                            UAS
                        </th>

                    </tr>

                </thead>

                {{-- BODY --}}
                <tbody class="divide-y divide-gray-100 bg-white">

                    @foreach ($siswas as $index => $siswa)

                        <tr class="transition hover:bg-gray-50">

                            {{-- NAMA SISWA --}}
                            <td class="px-6 py-4 font-medium text-gray-800 whitespace-nowrap">
                                {{ $siswa['nama_lengkap'] }}
                            </td>

                            {{-- TUGAS --}}
                            <td class="px-3 py-3">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    wire:model="siswas.{{ $index }}.tugas"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        text-center
                                        shadow-sm
                                        focus:border-primary-500
                                        focus:ring-primary-500
                                    "
                                >
                            </td>

                            {{-- HARIAN --}}
                            <td class="px-3 py-3">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    wire:model="siswas.{{ $index }}.harian"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        text-center
                                        shadow-sm
                                        focus:border-primary-500
                                        focus:ring-primary-500
                                    "
                                >
                            </td>

                            {{-- UTS --}}
                            <td class="px-3 py-3">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    wire:model="siswas.{{ $index }}.uts"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        text-center
                                        shadow-sm
                                        focus:border-primary-500
                                        focus:ring-primary-500
                                    "
                                >
                            </td>

                            {{-- UAS --}}
                            <td class="px-3 py-3">
                                <input
                                    type="number"
                                    min="0"
                                    max="100"
                                    wire:model="siswas.{{ $index }}.uas"
                                    class="
                                        w-full
                                        rounded-lg
                                        border-gray-300
                                        text-center
                                        shadow-sm
                                        focus:border-primary-500
                                        focus:ring-primary-500
                                    "
                                >
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

    {{-- BUTTON --}}
    <div class="mt- flex justify-end">

        <x-filament::button
            size="lg"
            wire:click="simpan"
        >
            Simpan Nilai
        </x-filament::button>

    </div>

</x-filament-panels::page>