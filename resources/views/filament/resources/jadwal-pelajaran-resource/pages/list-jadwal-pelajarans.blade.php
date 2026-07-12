<x-filament::page>

<div class="space-y-6">

    {{-- ========================================================= --}}
    {{-- FILTER --}}
    {{-- ========================================================= --}}

    <x-filament::section>

        {{ $this->form }}

    </x-filament::section>

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="flex items-center justify-between">

        <div>

            <h2 class="text-2xl font-bold tracking-tight">

                Jadwal Pelajaran

            </h2>

            <p class="mt-1 text-sm text-gray-500">

                Generate jadwal otomatis maupun kelola jadwal secara manual.

            </p>

        </div>

        <x-filament::button
            color="primary"
            icon="heroicon-o-bolt"
            wire:click="generate"
            :disabled="!$kelas_id">

            Generate Jadwal

        </x-filament::button>

    </div>

    {{-- ========================================================= --}}
    {{-- GRID --}}
    {{-- ========================================================= --}}

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">

        <table class="w-full table-fixed border-collapse">

            <thead>

                <tr class="bg-gray-100">

                    <th
                        class="w-56 border border-gray-200 px-4 py-4 text-left font-semibold">

                        Jam Pelajaran

                    </th>

                    @foreach ([
                        'Senin',
                        'Selasa',
                        'Rabu',
                        'Kamis',
                        'Jumat',
                        'Sabtu',
                    ] as $hari)

                        <th
                            class="border border-gray-200 px-4 py-4 text-center font-semibold">

                            {{ $hari }}

                        </th>

                    @endforeach

                </tr>

            </thead>

            <tbody>

@forelse ($grid as $row)

    <tr class="hover:bg-gray-50">

        {{-- ===================================================== --}}
        {{-- JAM PELAJARAN --}}
        {{-- ===================================================== --}}

        <td class="border border-gray-200 bg-gray-50 px-4 py-4 align-top">

            <div class="font-semibold">

                {{ $row['jam']->nama }}

            </div>

            <div class="mt-1 text-xs text-gray-500">

                {{ \Carbon\Carbon::parse($row['jam']->jam_mulai)->format('H:i') }}
                -
                {{ \Carbon\Carbon::parse($row['jam']->jam_selesai)->format('H:i') }}

            </div>

        </td>

        {{-- ===================================================== --}}
        {{-- KOLOM HARI --}}
        {{-- ===================================================== --}}

        @foreach ([
            'Senin',
            'Selasa',
            'Rabu',
            'Kamis',
            'Jumat',
            'Sabtu',
        ] as $hari)

            @php

                $item = $row['hari'][$hari] ?? null;

            @endphp

            <td
                class="border border-gray-200 p-2 align-top">

                @if ($item)

                    <div
                        class="flex min-h-[110px] flex-col justify-between rounded-lg border border-primary-200 bg-primary-50 p-3 transition hover:shadow">

                        <div>

                            <div class="font-semibold text-primary-700">

                                {{ $item->mataPelajaran?->nama }}

                            </div>

                            <div class="mt-1 text-sm text-gray-600">

                                {{ $item->guru?->nama }}

                            </div>

                        </div>

                        <div class="mt-3 flex items-center justify-start">

                            <button
                                type="button"
                                wire:click="delete('{{ $hari }}', {{ $row['jam']->id }})"
                                class="rounded-md p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                title="Hapus Jadwal">
                        
                                <x-heroicon-o-trash class="h-4 w-4" />
                        
                            </button>
                        
                        </div>

                    </div>

                @else

                    <button
                        type="button"
                        wire:click="openModal('{{ $hari }}', {{ $row['jam']->id }})"
                        @disabled(!$kelas_id)
                        class="flex min-h-[110px] w-full items-center justify-center rounded-lg border-2 border-dashed border-gray-300 transition hover:border-primary-500 hover:bg-primary-50 disabled:cursor-not-allowed disabled:opacity-40">

                        <span class="text-3xl text-gray-300">

                            +

                        </span>

                    </button>

                @endif

            </td>

        @endforeach

    </tr>

@empty

    <tr>

        <td
            colspan="7"
            class="py-12 text-center text-gray-500">

            Pilih kelas untuk menampilkan jadwal pelajaran.

        </td>

    </tr>

@endforelse

</tbody>

        </table>

    </div>
    
{{-- ========================================================= --}}
{{-- MODAL INPUT JADWAL --}}
{{-- ========================================================= --}}

<x-filament::modal
    id="input-jadwal"
    width="md">

    <div class="space-y-6">

        {{-- Header --}}
        <div>

            <h3 class="text-xl font-semibold tracking-tight">

                Tambah Jadwal

            </h3>

            <p class="mt-1 text-sm text-gray-500">

                Pilih mata pelajaran dan guru.

            </p>

        </div>

        {{-- Mata Pelajaran --}}
        <div>

            <label class="mb-2 block text-sm font-medium">

                Mata Pelajaran

            </label>

            <select
                wire:model.live="mapel_id"
                class="w-full rounded-xl border-gray-300">

                <option value="">
                    Pilih Mata Pelajaran
                </option>

                @foreach ($this->mataPelajaran as $mapel)

                    <option value="{{ $mapel->id }}">
                        {{ $mapel->nama }}
                    </option>

                @endforeach

            </select>

        </div>

        {{-- Guru --}}
        <div>

            <label class="mb-2 block text-sm font-medium">

                Guru

            </label>

            <select
                wire:model.live="guru_id"
                class="w-full rounded-xl border-gray-300">

                <option value="">
                    Pilih Guru
                </option>

                @foreach ($this->guru as $guru)

                    <option value="{{ $guru->id }}">
                        {{ $guru->nama }}
                    </option>

                @endforeach

            </select>

        </div>

        {{-- Action --}}
        <div class="flex justify-end gap-2 pt-3">

            <x-filament::button
                color="gray"
                outlined
                x-on:click="$dispatch('close-modal', { id: 'input-jadwal' })">

                Batal

            </x-filament::button>

            <x-filament::button
                color="primary"
                icon="heroicon-o-check"
                wire:click="save">

                Simpan

            </x-filament::button>

        </div>

    </div>

</x-filament::modal>

</div>

</x-filament::page>