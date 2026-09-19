@extends('wali.layout.wali')

@section('content')

<div class="p-4 space-y-5">

    {{-- HERO (SAMA SEPERTI RAPORT BIASA, BIAR KONSISTEN) --}}
    <div class="
        relative
        overflow-hidden
        rounded-[28px]

        bg-gradient-to-br
        from-[#00A39D]
        via-[#00B4AC]
        to-[#14C8C0]

        p-5
        text-white
        shadow-xl
    ">

        <div class="text-sm text-white/80">
            Raport Digital
        </div>

        <div class="text-xl font-bold mt-1">
            {{ $siswa->nama_lengkap ?? '-' }}
        </div>

    </div>

    {{-- PESAN TERKUNCI --}}
    <div class="bg-white rounded-[28px] border border-slate-100 shadow-sm p-8 text-center">

        <div class="
            w-16 h-16
            rounded-2xl
            bg-amber-50
            border border-amber-100
            flex items-center justify-center
            mx-auto mb-4
        ">
            <x-heroicon-o-lock-closed class="w-8 h-8 text-amber-500" />
        </div>

        <div class="text-lg font-bold text-slate-900">
            Raport Belum Bisa Diakses
        </div>

        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            Akses raport digital untuk siswa ini sedang dikunci sekolah,
            biasanya karena masih ada tagihan yang belum lunas.
            Silakan lunasi tagihan terlebih dahulu, atau hubungi pihak
            sekolah kalau ada pertanyaan.
        </p>

        <a
            href="{{ route('wali.keuangan') }}"
            class="
                inline-flex items-center justify-center gap-2
                mt-6 px-6 py-3 rounded-2xl
                bg-[#00A39D] hover:bg-[#018983]
                text-sm font-semibold text-white
                shadow-sm transition-colors
            ">
            Lihat & Bayar Tagihan
        </a>

    </div>

</div>

@endsection
