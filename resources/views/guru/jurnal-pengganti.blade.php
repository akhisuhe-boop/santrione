@extends('guru.layout.guru')

@section('content')

<div class="min-h-screen bg-[#F8FAFB] px-4 pb-8">

    {{-- HEADER --}}
    <div class="pt-5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <x-heroicon-o-user-group class="w-5 h-5 text-amber-500" />
        </div>
        <div class="min-w-0">
            <div class="text-[15px] font-bold tracking-tight text-slate-900">
                Isi Jurnal Pengganti
            </div>
            <div class="text-xs text-slate-500 mt-0.5">
                {{ now()->locale('id')->translatedFormat('l, d F Y') }}
            </div>
            <div class="text-xs text-slate-400 mt-0.5">
                Pilih kelas yang ingin digantikan
            </div>
        </div>
    </div>

    <div class="mt-5 space-y-2.5">
        @forelse($jadwalList as $jadwal)
            <form method="POST" action="{{ route('guru.jurnal.pengganti.isi') }}">
                @csrf
                <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
                <button type="submit"
                    class="group w-full text-left rounded-2xl border border-slate-100 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-all duration-200 hover:shadow-[0_10px_28px_-6px_rgba(15,23,42,0.12)] hover:-translate-y-0.5 hover:border-amber-200">
                    <div class="flex items-center gap-3">

                        <div class="w-11 h-11 rounded-xl bg-amber-500 shadow-md shadow-amber-500/25 flex items-center justify-center shrink-0">
                            <x-heroicon-o-book-open class="w-5 h-5 text-white" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-[13px] text-slate-900 truncate">
                                {{ $jadwal->mataPelajaran->nama ?? '-' }} &middot; {{ $jadwal->kelas->nama ?? '-' }}
                            </div>
                            <div class="mt-1 flex items-center gap-1 text-[11px] text-slate-500">
                                <x-heroicon-o-clock class="w-3.5 h-3.5 shrink-0" />
                                {{ $jadwal->jamPelajaran->nama ?? '-' }}
                                ({{ date('H:i', strtotime($jadwal->jamPelajaran->jam_mulai)) }}–{{ date('H:i', strtotime($jadwal->jamPelajaran->jam_selesai)) }})
                            </div>
                            <div class="mt-1 flex items-center gap-1 text-[11px] text-amber-600">
                                <x-heroicon-o-user class="w-3.5 h-3.5 shrink-0" />
                                Guru asli: {{ $jadwal->guru->nama ?? '-' }}
                            </div>
                        </div>

                        <span class="shrink-0 inline-flex items-center gap-1 rounded-xl bg-[#00A39D]/10 px-3 py-2 text-[11px] font-semibold text-[#00A39D] transition group-hover:bg-[#00A39D] group-hover:text-white">
                            Gantikan
                            <x-heroicon-o-arrow-right class="w-3.5 h-3.5" />
                        </span>

                    </div>
                </button>
            </form>
        @empty
            <div class="rounded-2xl border border-slate-100 bg-white p-8 text-center shadow-sm">
                <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-check-circle class="w-6 h-6 text-slate-300" />
                </div>
                <div class="text-[13px] font-semibold text-slate-700">
                    Tidak ada kelas kosong
                </div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Belum ada kelas yang perlu digantikan hari ini
                </div>
            </div>
        @endforelse
    </div>

</div>

@endsection
