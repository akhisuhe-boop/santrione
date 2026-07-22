@extends('guru.layout.guru')

@section('content')

<div class="min-h-screen bg-[#F8FAFB] px-4 pb-8">

    <div class="pt-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-[28px] font-bold tracking-tight text-slate-900">
                    Isi Jurnal Pengganti
                </div>
                <div class="mt-1 text-sm text-slate-500">
                    {{ now()->locale('id')->translatedFormat('l, d F Y') }} — Pilih kelas yang mau kamu gantikan
                </div>
            </div>
            <a href="{{ route('guru.dashboard') }}"
               class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-[#00A39D] hover:text-[#00A39D]">
                Dashboard
            </a>
        </div>
    </div>

    <div class="mt-6 space-y-3">
        @forelse($jadwalList as $jadwal)
            <form method="POST" action="{{ route('guru.jurnal.pengganti.isi') }}">
                @csrf
                <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
                <button type="submit"
                    class="w-full text-left rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-[#00A39D]">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-slate-900">
                                {{ $jadwal->mataPelajaran->nama ?? '-' }} — {{ $jadwal->kelas->nama ?? '-' }}
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                {{ $jadwal->jamPelajaran->nama ?? '-' }}
                                ({{ date('H:i', strtotime($jadwal->jamPelajaran->jam_mulai)) }}–{{ date('H:i', strtotime($jadwal->jamPelajaran->jam_selesai)) }})
                            </div>
                            <div class="mt-1 text-xs text-amber-600">
                                Guru asli: {{ $jadwal->guru->nama ?? '-' }}
                            </div>
                        </div>
                        <span class="rounded-full bg-[#00A39D]/10 px-3 py-1 text-xs font-medium text-[#00A39D]">
                            Gantikan
                        </span>
                    </div>
                </button>
            </form>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500">
                Tidak ada kelas yang perlu digantikan hari ini.
            </div>
        @endforelse
    </div>

</div>

@endsection
