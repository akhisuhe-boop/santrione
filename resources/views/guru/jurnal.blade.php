@extends('guru.layout.guru')

@section('content')

<div class="min-h-screen bg-[#F8FAFB] px-4 pb-8">

    <div class="pt-5">

        <div class="flex items-start justify-between gap-4">

            <div>

                <div class="text-[28px] font-bold tracking-tight text-slate-900">
                    Isi Jurnal
                </div>

                <div class="mt-1 text-sm text-slate-500">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>

            </div>

            <a href="{{ route('guru.dashboard') }}"
               class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-[#00A39D] hover:text-[#00A39D]">

                Dashboard

            </a>

        </div>

    </div>

    <div
        class="relative mt-6 overflow-hidden rounded-[30px]
               bg-gradient-to-br
               from-[#00A39D]
               via-[#00B4AC]
               to-[#14C8C0]
               p-6
               text-white
               shadow-xl">

        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-10 -left-10 h-28 w-28 rounded-full bg-white/10"></div>

        <div class="relative z-10">

            <div class="text-xs uppercase tracking-[3px] text-white/70">
                Mata Pelajaran
            </div>

            <div class="mt-2 text-3xl font-bold">
                {{ $jadwal->mataPelajaran->nama }}
            </div>

            <div class="mt-5 flex flex-wrap gap-2">

                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-medium backdrop-blur">
                    {{ $jadwal->kelas->nama }}
                </span>

                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-medium backdrop-blur">
                    JP {{ $jadwal->jamPelajaran->urutan }}
                </span>

                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-medium backdrop-blur">
                    {{ $jadwal->jamPelajaran->durasi_jp }} JP
                </span>

                <span class="rounded-full bg-white/15 px-3 py-1 text-[11px] font-medium backdrop-blur">
                    {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                </span>

            </div>

        </div>

    </div>
        <div class="mt-6 grid grid-cols-2 gap-4">
    </div>
    
        <div class="mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex items-center justify-between">

            <div>

                <div class="text-lg font-bold leading-none text-slate-900">
                    Materi Pembelajaran
                </div>
            
                <div class="mt-1 text-sm leading-5 text-slate-500">
                    Tuliskan materi pembelajaran
                </div>
            
            </div>

            <span
                class="rounded-full
                       bg-[#00A39D]/10
                       px-3
                       py-1
                       text-[11px]
                       font-semibold
                       text-[#00A39D]">

                Wajib Diisi

            </span>

        </div>

        <div class="mt-6">

            <label class="mb-2 block text-sm font-semibold text-slate-700">

                Materi

            </label>

            <textarea
                id="materi"
                name="materi"
                rows="8"
                maxlength="1000"
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-[14px] leading-7 text-slate-700 placeholder:text-slate-400 focus:border-[#00A39D] focus:bg-white focus:ring-4 focus:ring-[#00A39D]/10"
                placeholder="Contoh: Peserta didik mempelajari Bab 3 tentang Persamaan Linear Satu Variabel beserta latihan soal..."
            >{{ old('materi', $jurnal->materi) }}</textarea>

        </div>

    </div>

    <script>

        const materi = document.getElementById('materi');
        const counter = document.getElementById('counter');

        materi.addEventListener('input', function () {

            counter.innerText = this.value.length;

        });

    </script>
    

        <div class="mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="text-lg font-bold leading-tight text-slate-900">
                    Absensi Siswa
                </h2>
            
                <p class="mt-0.5 text-[13px] leading-5 text-slate-500">
                    Isi absensi mata pelajaran hari ini.
                </p>
            
            </div>

            <button
                id="btnAbsensi"
                type="button"
                class="rounded-2xl
                       border
                       border-[#00A39D]
                       bg-[#00A39D]/5
                       px-4
                       py-2
                       text-sm
                       font-medium
                       text-[#00A39D]">
            
                Isi Absensi
            
            </button>

        </div>

        <div
    id="panelAbsensi"
    class="hidden mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5">
    
    @foreach($absensis as $absen)

        <div class="py-5 border-b border-slate-200 last:border-0">

            <div class="font-semibold text-slate-800">

                {{ $absen->siswa->nama_lengkap }}

            </div>

            <div class="grid grid-cols-4 gap-2 mt-4">

                @foreach(['Hadir','Sakit','Izin','Alpha'] as $status)

                    <label>

                        <input
                            class="peer hidden"
                            type="radio"
                            name="absensi[{{ $absen->id }}]"
                            value="{{ $status }}"
                            {{ $absen->status==$status?'checked':'' }}>

                        <div
                            class="rounded-xl
                                   border
                                   border-slate-200
                                   bg-white
                                   py-2
                                   text-center
                                   text-sm
                                   font-medium
                                   transition
                                   cursor-pointer
                                   peer-checked:bg-[#00A39D]
                                   peer-checked:border-[#00A39D]
                                   peer-checked:text-white">

                            {{ $status }}

                        </div>

                    </label>

                @endforeach

            </div>

        </div>

    @endforeach

</div>
        </div>

        <div class="mt-6 rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">

        <div class="flex items-center justify-between">

            <div>

                <div class="text-lg font-bold text-slate-900">
                    Ringkasan Jurnal
                </div>

                <div class="mt-1 text-sm text-slate-500">
                    Periksa kembali data jurnal mengajar.
                </div>

            </div>
            
        </div>

        <div class="mt-6 divide-y divide-slate-100 rounded-3xl border border-slate-200">

            <div class="flex items-center justify-between px-5 py-4">

                <span class="text-sm text-slate-500">
                    Mata Pelajaran
                </span>

                <span class="text-sm font-semibold text-slate-900">
                    {{ $jadwal->mataPelajaran->nama }}
                </span>

            </div>

            <div class="flex items-center justify-between px-5 py-4">

                <span class="text-sm text-slate-500">
                    Kelas
                </span>

                <span class="text-sm font-semibold text-slate-900">
                    {{ $jadwal->kelas->nama }}
                </span>

            </div>

            <div class="flex items-center justify-between px-5 py-4">

                <span class="text-sm text-slate-500">
                    Jam Pelajaran
                </span>

                <span class="text-sm font-semibold text-slate-900">
                    Jam Pelajaran Ke {{ $jadwal->jamPelajaran->urutan }}
                </span>

            </div>

            <div class="flex items-center justify-between px-5 py-4">

                <span class="text-sm text-slate-500">
                    Durasi
                </span>

                <span class="text-sm font-semibold text-slate-900">
                    {{ $jadwal->jamPelajaran->durasi_jp }} JP
                </span>

            </div>

            <div class="flex items-center justify-between px-5 py-4">

                <span class="text-sm text-slate-500">
                    Kehadiran
                </span>

                <span class="text-sm font-semibold text-slate-900">
                    {{ $rekap['hadir'] ?? 0 }}/{{ $rekap['total'] ?? 0 }} Siswa Hadir
                </span>

            </div>

            <div class="flex items-center justify-between px-5 py-4">

                <span class="text-sm text-slate-500">
                    Status
                </span>

                <span
                    class="rounded-full px-3 py-1 text-xs font-semibold
                    {{ ($jurnal->status ?? old('status')) == 'Selesai'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-amber-100 text-amber-700' }}">

                    {{ old('status', $jurnal->status) }}

                </span>

            </div>

        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">

            <a
                href="{{ route('guru.dashboard') }}"
                class="flex-1 rounded-2xl border border-slate-200 bg-white py-4 text-center font-semibold text-slate-600 transition hover:border-slate-300">

                Batal

            </a>

            <button
                type="submit"
                class="flex-1 rounded-2xl
                       bg-gradient-to-r
                       from-[#00A39D]
                       via-[#00B4AC]
                       to-[#14C8C0]
                       py-4
                       text-center
                       font-semibold
                       text-white
                       shadow-lg
                       transition
                       hover:scale-[1.01]
                       hover:shadow-xl
                       active:scale-[0.99]">

                Simpan Jurnal Mengajar

            </button>

        </div>

    </div>
    
        @if(session('success'))

        <div
            class="mt-6 rounded-3xl
                   border border-emerald-200
                   bg-emerald-50
                   px-5 py-4">

            <div class="font-semibold text-emerald-700">
                {{ session('success') }}
            </div>

        </div>

    @endif

    @if($errors->any())

        <div
            class="mt-6 rounded-3xl
                   border border-rose-200
                   bg-rose-50
                   px-5 py-4">

            <div class="font-semibold text-rose-700">
                Terjadi kesalahan.
            </div>

            <ul class="mt-2 space-y-1 text-sm text-rose-600">

                @foreach($errors->all() as $error)

                    <li>• {{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    </form>

    <div class="h-10"></div>

</div>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const textarea = document.getElementById('materi');

    if (textarea) {

        const resize = () => {

            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';

        };

        resize();

        textarea.addEventListener('input', resize);

    }

    const form = document.querySelector('form');

    if (form) {

        form.addEventListener('submit', function () {

            const button = form.querySelector('button[type="submit"]');

            if (!button) return;

            button.disabled = true;

            button.innerHTML = `
                <div class="flex items-center justify-center gap-2">
                    <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-90" fill="currentColor"
                              d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"/>
                    </svg>
                    <span>Menyimpan Jurnal...</span>
                </div>
            `;

        });

    }

});

</script>

<script>

document
.getElementById('btnAbsensi')
.addEventListener('click', function(){

    document
    .getElementById('panelAbsensi')
    .classList.toggle('hidden');

});

</script>

@endsection