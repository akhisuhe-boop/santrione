@extends('wali.layout.wali')

@section('content')

<div class="p-4 space-y-6" x-data="{ showAll: false }">

    {{-- ================= HERO ================= --}}
    <div class="relative overflow-hidden rounded-[28px]
        bg-gradient-to-br from-[#00A39D] via-[#00B4AC] to-[#14C8C0]
        p-6 text-white shadow-xl">

        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>

        <div class="relative z-10">
            <div class="text-white/80 text-sm">
                Absensi Sekolah
            </div>

            <h1 class="text-2xl font-bold">
                Ajukan Izin / Sakit
            </h1>

            <p class="text-white/70 text-xs mt-1">
                Ajukan izin tidak masuk sekolah untuk ananda, pantau status persetujuan admin
            </p>

            {{-- SUMMARY --}}
            <div class="mt-4 grid grid-cols-2 gap-3">

                <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                    <div class="text-xs text-white/70">Total Pengajuan</div>
                    <div class="text-2xl font-bold mt-1">
                        {{ $izinHarians->count() }}
                    </div>
                </div>

                <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                    <div class="text-xs text-white/70">Status Terbaru</div>
                    <div class="text-lg font-bold mt-1">
                        {{ $izinHarians->first()->status ?? '-' }}
                    </div>
                </div>

            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- FORM --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <x-heroicon-o-document-text class="w-5 h-5 text-[#00A39D]" />

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 leading-tight">
                        Form Pengajuan Izin / Sakit
                    </h3>
                    <p class="text-[11px] text-slate-500">
                        Ananda tidak masuk sekolah karena izin atau sakit
                    </p>
                </div>
            </div>
        </div>

        <div class="p-4 pt-3">

            <form method="POST" action="{{ route('wali.izin-tidak-masuk.store') }}"
                enctype="multipart/form-data"
                class="space-y-3">

                @csrf

                {{-- JENIS --}}
                <div>
                    <label class="text-xs text-slate-500">Jenis</label>
                    <select name="jenis" required
                        class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                        focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition">
                        <option value="Izin">Izin</option>
                        <option value="Sakit">Sakit</option>
                    </select>
                </div>

                {{-- TANGGAL --}}
                <div class="grid grid-cols-2 gap-2.5">

                    <div>
                        <label class="text-xs text-slate-500">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai" required
                            class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                            focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition">
                    </div>

                    <div>
                        <label class="text-xs text-slate-500">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai" required
                            class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                            focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition">
                    </div>

                </div>

                {{-- KETERANGAN --}}
                <div>
                    <label class="text-xs text-slate-500">Keterangan</label>
                    <textarea name="keterangan" rows="2" required
                        class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                        focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition"
                        placeholder="Jelaskan alasan izin/sakit..."></textarea>
                </div>

                {{-- LAMPIRAN --}}
                <div>
                    <label class="text-xs text-slate-500">Lampiran (opsional, contoh: surat dokter)</label>
                    <input type="file" name="lampiran" accept=".jpg,.jpeg,.png,.pdf"
                        class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                        focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition">
                </div>

                {{-- BUTTON --}}
                <button type="submit"
                    class="w-full py-2.5 rounded-xl bg-[#00A39D] text-white text-sm font-semibold
                    hover:bg-[#018a85] active:scale-[0.99] transition">

                    <div class="flex items-center justify-center gap-2">
                        <x-heroicon-o-paper-airplane class="w-4 h-4" />
                        Kirim Pengajuan
                    </div>

                </button>

            </form>

        </div>
    </div>

    {{-- ================= RIWAYAT ================= --}}
    <div class="bg-white rounded-[28px] border border-slate-100 shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-slate-100 bg-[#00A39D]/5">

            <div class="flex items-center justify-between">

                <div>

                    <h3 class="font-semibold text-slate-900 flex items-center gap-2">

                        <span class="w-7 h-7 rounded-lg bg-white border border-[#00A39D]/10 flex items-center justify-center">
                            <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-[#00A39D]" />
                        </span>

                        Riwayat Pengajuan
                    </h3>

                    <p class="text-xs text-slate-500 mt-1">
                        Data izin/sakit ananda yang sudah diajukan
                    </p>

                </div>

                <div class="px-3 py-1 rounded-full bg-white border border-slate-100 text-xs text-slate-500 flex items-center gap-1">
                    <x-heroicon-o-rectangle-stack class="w-3.5 h-3.5 text-slate-400" />
                    {{ $izinHarians->count() }} data
                </div>

            </div>

        </div>

        @forelse($izinHarians as $index => $izin)

        <div
            x-show="showAll || {{ $index }} < 3"
            x-transition.duration.200ms
            class="
                px-4 py-4
                hover:bg-slate-50/70
                transition
                flex items-start justify-between
                {{ !$loop->last ? 'border-b border-slate-100' : '' }}
            ">

            <div class="flex items-start gap-3">

                <div class="w-10 h-10 rounded-xl bg-[#00A39D]/10 flex items-center justify-center shrink-0">
                    @if($izin->jenis === 'Sakit')
                        <x-heroicon-o-heart class="w-5 h-5 text-[#00A39D]" />
                    @else
                        <x-heroicon-o-document-text class="w-5 h-5 text-[#00A39D]" />
                    @endif
                </div>

                <div>

                    <div class="flex items-center gap-2">

                        <div class="font-semibold text-sm text-slate-900">
                            {{ $izin->jenis }}
                        </div>

                        @if($izin->status == 'pending')
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                Pending
                            </span>
                        @elseif($izin->status == 'approved')
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600">
                                Disetujui
                            </span>
                        @else
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-red-50 text-red-500">
                                Ditolak
                            </span>
                        @endif

                    </div>

                    <div class="text-sm text-slate-500 mt-1">
                        {{ $izin->keterangan }}
                    </div>

                    <div class="flex items-center gap-2 text-xs text-slate-500 mt-1">
                        <x-heroicon-o-calendar class="w-3.5 h-3.5 text-slate-400" />
                        <div class="flex items-center gap-1">
                            <span class="font-medium text-slate-600">
                                {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d M Y') }}
                            </span>
                            <span class="text-slate-400">→</span>
                            <span class="font-medium text-slate-600">
                                {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d M Y') }}
                            </span>
                        </div>
                    </div>

                    @if($izin->status === 'ditolak' && $izin->catatan_admin)
                        <div class="text-xs text-red-500 mt-1">
                            Alasan: {{ $izin->catatan_admin }}
                        </div>
                    @endif

                </div>

            </div>

        </div>

        @empty

        <div class="p-10 text-center">

            <div class="w-16 h-16 rounded-3xl bg-slate-50 mx-auto flex items-center justify-center">
                <x-heroicon-o-inbox class="w-7 h-7 text-slate-400" />
            </div>

            <div class="font-semibold text-slate-900 mt-4">
                Belum Ada Pengajuan
            </div>

            <div class="text-sm text-slate-500 mt-1">
                Semua pengajuan izin/sakit akan tampil di sini
            </div>

        </div>

        @endforelse

        @if($izinHarians->count() > 3)
            <button @click="showAll = !showAll"
                class="w-full py-3 text-sm text-[#00A39D] font-medium border-t border-slate-100">
                <span x-show="!showAll">Tampilkan Semua</span>
                <span x-show="showAll">Tampilkan Sedikit</span>
            </button>
        @endif

    </div>

</div>

@endsection
