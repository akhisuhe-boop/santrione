<x-filament::page>

@php
    $haris = ['senin','selasa','rabu','kamis','jumat','sabtu'];
@endphp

<div class="space-y-5">

    {{-- 🔥 FILTER (FILAMENT STYLE) --}}
    <div class="bg-white p-4 rounded-xl shadow">
        {{ $this->form }}
    </div>

    {{-- HEADER --}}
        <div class="flex justify-between items-center mt-6 mb-6">
        <h2 class="text-xl font-bold">Jadwal Pelajaran</h2>

        <button
            wire:click="generate"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
            ⚡ Generate Otomatis
        </button>
    </div>

    {{-- GRID --}}
<div class="overflow-auto border rounded-xl bg-white mt-6">

        <table class="w-full text-sm">
            
            {{-- HEADER --}}
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left w-24">Jam</th>

                    @foreach ($haris as $h)
                        <th class="p-3 text-left capitalize">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>

            {{-- BODY --}}
            <tbody>
                @for ($jam = 1; $jam <= 8; $jam++)
                <tr class="border-t">

                    {{-- JAM --}}
                    <td class="p-3 font-medium whitespace-nowrap bg-gray-50">
                        Jam ke - {{ $jam }}
                    </td>

                    {{-- HARI --}}
                    @foreach ($haris as $hari)
                    <td class="p-2 align-top">

                        @php
                            $item = $jadwal[$hari][$jam] ?? null;
                            $prev = $jadwal[$hari][$jam - 1] ?? null;
                        @endphp

                        {{-- 🔥 SKIP SLOT KE-2 (biar 2 JP jadi 1 kartu) --}}
                        @if($item && $prev 
                            && $prev->mata_pelajaran_id === $item->mata_pelajaran_id 
                            && $prev->pegawai_id === $item->pegawai_id)

                            {{-- kosong --}}

                        {{-- 🔥 SLOT ISI (AWAL PERTEMUAN) --}}
                        @elseif($item)

                        <div class="bg-blue-100 p-3 rounded-xl flex justify-between items-start gap-2 h-[120px] shadow-sm hover:shadow transition">

                            <div class="space-y-1">
                                <div class="font-semibold text-sm leading-tight">
                                    {{ $item->mataPelajaran->nama ?? '-' }}
                                </div>

                                <div class="text-xs text-gray-600">
                                    {{ $item->guru->nama ?? '-' }}
                                </div>

                                <div class="text-[10px] text-gray-400">
                                    2 JP (1 Pertemuan)
                                </div>
                            </div>

                            <button
                                wire:click="delete('{{ $hari }}', {{ $jam }})"
                                class="text-xs text-red-500 hover:text-red-700">
                                ✕
                            </button>

                        </div>

                        {{-- 🔥 SLOT KOSONG --}}
                        @else

                        <button
                            @if(!$kelas_id) disabled @endif
                            wire:click="openModal('{{ $hari }}', {{ $jam }})"
                            class="w-full h-[120px] border-dashed border rounded-xl
                            flex items-center justify-center text-gray-400 text-lg
                            {{ !$kelas_id ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-50' }}">
                            +
                        </button>

                        @endif

                    </td>
                    @endforeach

                </tr>
                @endfor
            </tbody>

        </table>

    </div>

</div>

{{-- MODAL --}}
<x-filament::modal id="input-jadwal">

    <div class="space-y-3">

        <select wire:model.defer="mapel_id" class="w-full border rounded p-2">
            <option value="">Pilih Mata Pelajaran</option>
            @foreach(\App\Models\MataPelajaran::all() as $m)
                <option value="{{ $m->id }}">{{ $m->nama }}</option>
            @endforeach
        </select>

        <select wire:model.defer="guru_id" class="w-full border rounded p-2">
            <option value="">Pilih Guru</option>
            @foreach(\App\Models\Pegawai::all() as $g)
                <option value="{{ $g->id }}">{{ $g->nama }}</option>
            @endforeach
        </select>

        <button
            wire:click="save"
            class="w-full bg-primary-600 text-white py-2 rounded-lg hover:bg-primary-700">
            Simpan
        </button>

    </div>

</x-filament::modal>

</x-filament::page>