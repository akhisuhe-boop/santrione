@extends('guru.layout.guru')

@section('content')

<div class="p-4 bg-slate-50 min-h-screen">

@if(session('success'))

<div
    id="alertSuccess"
    class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">

    <div class="flex items-start gap-3">

        <div
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500 text-white">

            ✓

        </div>

        <div class="flex-1">

            <div class="font-semibold text-emerald-800">
                Berhasil
            </div>

            <div class="text-sm text-emerald-700 mt-1">
                {{ session('success') }}
            </div>

        </div>

    </div>

</div>

<script>
setTimeout(() => {
    document.getElementById('alertSuccess')?.remove();
}, 3000);
</script>

@endif

<form
    action="{{ route('guru.nilai') }}"
    method="GET">

    {{-- ================= HERO ================= --}}
    <div
        class="relative overflow-hidden rounded-3xl
               bg-gradient-to-br
               from-[#00A39D]
               via-[#00B4AC]
               to-[#14C8C0]
               p-6
               text-white
               shadow-lg
               mb-6">

        <div class="absolute -top-16 -right-16 w-48 h-48 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-white/5 rounded-full"></div>

        <div class="relative z-10">

            <div class="text-white/80 text-sm">
                Teacher Assessment
            </div>

            <h1 class="text-2xl font-bold mt-1">
                Input Nilai Siswa
            </h1>

            <p class="text-white/80 text-sm mt-1">
                Input nilai harian, tugas, UTS maupun UAS siswa.
            </p>

        </div>

    </div>

    {{-- ================= FILTER ================= --}}
    <div
        class="bg-white
               border
               border-slate-200
               rounded-3xl
               overflow-hidden
               shadow-sm">

        {{-- HEADER --}}
        <div
            class="px-5 py-3
                   border-b
                   border-slate-100
                   bg-slate-50">

            <div class="text-sm font-semibold text-slate-900">
                Filter Penilaian
            </div>

            <div class="text-[13px] text-slate-500 mt-1">
                Pilih jadwal mengajar
            </div>

        </div>

        {{-- BODY --}}
        <div class="p-5 space-y-5">

            {{-- Kelas yang Diampu --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Kelas yang Diampu
                </label>

                <select
                    name="jadwal_id"
                    onchange="this.form.submit()"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm
                           focus:ring-2 focus:ring-[#00A39D] focus:border-[#00A39D]">

                    <option value="">Pilih Kelas</option>

                    @foreach($jadwals as $item)
                        <option
                            value="{{ $item->id }}"
                            @selected(request('jadwal_id') == $item->id)>
                            {{ $item->kelas->nama }} • {{ $item->mataPelajaran->nama }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- Jenis Penilaian --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                    Jenis Penilaian
                </label>

                <select
                    name="tipe_nilai"
                    onchange="this.form.submit()"
                    class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm
                           focus:ring-2 focus:ring-[#00A39D] focus:border-[#00A39D]">

                    <option value="">Pilih Jenis Penilaian</option>
                    <option value="tugas" @selected(request('tipe_nilai') == 'tugas')>Tugas</option>
                    <option value="harian" @selected(request('tipe_nilai') == 'harian')>Harian</option>
                    <option value="uts" @selected(request('tipe_nilai') == 'uts')>UTS</option>
                    <option value="uas" @selected(request('tipe_nilai') == 'uas')>UAS</option>

                </select>

                <p class="text-xs text-slate-400 mt-1.5">
                    Nilai yang sudah tersimpan untuk jenis ini akan otomatis tampil di bawah.
                </p>
            </div>

        </div>

    </div>

</form>

{{-- Jarak dengan Card Input Nilai --}}
<div class="mt-6"></div>

    {{-- ========================= --}}
    {{-- TABEL INPUT NILAI --}}
    {{-- ========================= --}}

    @if(isset($siswas) && $siswas->count() && request('jadwal_id') && !request('tipe_nilai'))

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm text-amber-800">
            Pilih <span class="font-semibold">Jenis Penilaian</span> di atas dulu untuk mulai input/lihat nilai.
        </div>

    @endif

    @if(isset($siswas) && $siswas->count() && request('tipe_nilai'))

    <form
        action="{{ route('guru.nilai.store') }}"
        method="POST">

        @csrf

        <input
        type="hidden"
        name="jadwal_id"
        value="{{ request('jadwal_id') }}">

        <input
        type="hidden"
        name="tipe_nilai"
        value="{{ request('tipe_nilai') }}">

        <div
        class="bg-white
               border
               border-slate-200
               rounded-3xl
               overflow-hidden
               shadow-sm
               mt-6">
    
        {{-- ================= HEADER ================= --}}
        <div
            class="px-5 py-4
                   border-b
                   border-slate-100
                   bg-slate-50">
    
            <div class="flex items-center justify-between">
    
                <div>

                    <div class="text-sm font-semibold text-slate-900">
                        Input Nilai
                    </div>
                
                    <div class="text-[13px] text-slate-500 mt-1">
                        Masukkan nilai siswa.
                    </div>
                
                </div>
    
                <div
                    class="px-4 py-1.5
                           rounded-full
                           bg-white
                           border border-slate-200
                           text-sm text-slate-600">
    
                    {{ $siswas->count() }} Siswa
    
                </div>
    
            </div>
    
        </div>
    
        {{-- ================= JENIS PENILAIAN AKTIF ================= --}}
        <div class="p-5 border-b border-slate-100">

            <label class="block text-[13px] font-medium text-slate-700 mb-2">
                Jenis Penilaian
            </label>

            <div
                class="inline-flex items-center gap-2
                       rounded-2xl border border-[#00A39D]/30
                       bg-[#00A39D]/10 px-4 py-2.5
                       text-sm font-semibold text-[#00807A]">

                {{ ['tugas' => 'Tugas', 'harian' => 'Harian', 'uts' => 'UTS', 'uas' => 'UAS'][request('tipe_nilai')] ?? request('tipe_nilai') }}

            </div>

            <p class="text-xs text-slate-400 mt-2">
                Mau input jenis penilaian lain? Ganti dulu di "Jenis Penilaian" pada Filter Penilaian di atas.
            </p>

        </div>
    
        {{-- ================= TABEL ================= --}}
        <div class="overflow-x-auto">
    
            <table class="w-full text-sm">
    
                <thead class="bg-slate-50">
    
                    <tr>
    
                        <th class="px-5 py-3 w-14">No</th>
                        <th class="px-5 py-3 text-left">Nama Siswa</th>
                        <th class="px-5 py-3 text-center w-36">Nilai</th>
    
                    </tr>
    
                </thead>
    
                <tbody>
    
                    @foreach($siswas as $siswa)
    
                    <tr class="border-t border-slate-100">
    
                        <td class="px-5 py-4">
                            {{ $loop->iteration }}
                        </td>
    
                        <td class="px-5 py-4 font-medium text-slate-900">
                            {{ $siswa->nama_lengkap }}
                        </td>
    
                        <td class="px-5 py-4 text-center">
    
                            <input
                                type="number"
                                name="nilai[{{ $siswa->id }}]"
                                min="0"
                                max="100"
                                placeholder="0-100"
                                value="{{ old('nilai.' . $siswa->id, $nilaiTersimpan[$siswa->id] ?? '') }}"
                                class="w-24 rounded-xl border border-slate-200
                                       py-2 text-center
                                       focus:ring-[#00A39D]
                                       focus:border-[#00A39D]">
    
                        </td>
    
                    </tr>
    
                    @endforeach
    
                </tbody>
    
            </table>
    
        </div>
    
        {{-- ================= FOOTER ================= --}}
        <div
            class="border-t border-slate-100
                   bg-slate-50
                   p-4">
    
            <div class="grid grid-cols-2 gap-3">

                <button
                    type="submit"
                    class="py-2.5 rounded-2xl bg-[#00A39D] hover:bg-[#018983]
                           text-sm font-semibold text-white
                           transition-colors">
            
                    Simpan Nilai
            
                </button>
            
                <button
                    type="submit"
                    form="resetNilaiForm"
                    onclick="return confirm('Yakin mau hapus nilai yang sudah tersimpan untuk jenis penilaian ini? Aksi ini tidak bisa dibatalkan.')"
                    class="py-2.5 rounded-2xl border border-red-200
                           bg-white text-center text-sm font-semibold
                           text-red-600
                           hover:bg-red-50 transition-colors">
            
                    Reset
            
                </button>
            
            </div>
    
        </div>
    
    </div>

    </form>

    <form
        id="resetNilaiForm"
        action="{{ route('guru.nilai.reset') }}"
        method="POST"
        style="display:none;">

        @csrf

        <input type="hidden" name="jadwal_id" value="{{ request('jadwal_id') }}">
        <input type="hidden" name="tipe_nilai" value="{{ request('tipe_nilai') }}">

    </form>

    @endif

</div>

@endsection