@extends('wali.layout.wali')

@section('content')

<div class="p-4 space-y-6">

    {{-- ================= HERO ================= --}}
    <div class="relative overflow-hidden rounded-[28px]
        bg-gradient-to-br from-[#00A39D] via-[#00B4AC] to-[#14C8C0]
        p-6 text-white shadow-xl">

        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>

        <div class="relative z-10">
            <div class="text-white/80 text-sm">
                Layanan Perizinan
            </div>

            <h1 class="text-2xl font-bold">
                Pengajuan Izin Santri
            </h1>

            <p class="text-white/70 text-xs mt-1">
                Ajukan izin dan pantau status persetujuan secara real-time
            </p>

            {{-- SUMMARY --}}
            <div class="mt-4 grid grid-cols-2 gap-3">

                <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                    <div class="text-xs text-white/70">Total Pengajuan</div>
                    <div class="text-2xl font-bold mt-1">
                        {{ $perizinans->count() }}
                    </div>
                </div>

                <div class="rounded-2xl bg-white/10 border border-white/10 p-3">
                    <div class="text-xs text-white/70">Status Terbaru</div>
                    <div class="text-lg font-bold mt-1">
                        {{ $perizinans->first()->status ?? '-' }}
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- FORM --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

        {{-- HEADER (lebih compact) --}}
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <x-heroicon-o-document-text class="w-5 h-5 text-[#00A39D]" />

                <div>
                    <h3 class="text-sm font-semibold text-slate-900 leading-tight">
                        Form Perizinan
                    </h3>
                    <p class="text-[11px] text-slate-500">
                        Sistem izin santri
                    </p>
                </div>
            </div>
        </div>

        {{-- BODY (dipadatkan) --}}
        <div class="p-4 pt-3">

            <form method="POST" action="{{ route('wali.perizinan.store') }}"
                class="space-y-3">

                @csrf

                {{-- TIPE --}}
                <div>
                    <label class="text-xs text-slate-500">Tipe Izin</label>
                    <select name="tipe"
                        class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                        focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition">
                        <option value="keluar">Izin Keluar</option>
                        <option value="pulang">Izin Pulang</option>
                    </select>
                </div>

                {{-- KEPELUAN --}}
                <div>
                    <label class="text-xs text-slate-500">Keperluan</label>
                    <textarea name="keperluan" rows="2"
                        class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                        focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition"
                        placeholder="Jelaskan keperluan izin..."></textarea>
                </div>

                {{-- TANGGAL --}}
                <div class="grid grid-cols-2 gap-2.5">

                    <div>
                        <label class="text-xs text-slate-500">Mulai</label>
                        <input type="date" name="tanggal_mulai"
                            class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                            focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition">
                    </div>

                    <div>
                        <label class="text-xs text-slate-500">Selesai</label>
                        <input type="date" name="tanggal_selesai"
                            class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                            focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition">
                    </div>

                </div>

                {{-- PENJEMPUT --}}
                <div>
                    <label class="text-xs text-slate-500">Penjemput</label>
                    <input type="text" name="penjemput"
                        class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                        focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition"
                        placeholder="Nama penjemput">
                </div>

                {{-- HUBUNGAN --}}
                <div>
                    <label class="text-xs text-slate-500">Hubungan</label>
                    <select name="hubungan"
                        class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                        focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition">

                        <option value="">Pilih hubungan</option>
                        <option value="ayah">Ayah</option>
                        <option value="ibu">Ibu</option>
                        <option value="kakek">Kakek</option>
                        <option value="nenek">Nenek</option>
                        <option value="paman">Paman</option>
                        <option value="bibi">Bibi</option>
                        <option value="kakak">Kakak</option>
                        <option value="adik">Adik</option>
                        <option value="wali">Wali</option>
                        <option value="lainnya">Lainnya</option>

                    </select>
                </div>

                {{-- NO WA --}}
                <div>
                    <label class="text-xs text-slate-500">No WhatsApp</label>
                    <input type="text" name="no_wa"
                        class="mt-1 w-full rounded-xl border-slate-200 bg-slate-50 px-3 py-3 text-sm
                        focus:bg-white focus:border-[#00A39D] focus:ring-2 focus:ring-[#00A39D]/20 transition"
                        placeholder="08xxxxxxxxxx">
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

    {{-- ================= RIWAYAT (STYLE SAMA PELANGGARAN KAMU) ================= --}}
    <div class="bg-white rounded-[28px] border border-slate-100 shadow-sm overflow-hidden">

        {{-- HEADER --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-[#00A39D]/5">

            <div class="flex items-center justify-between">

                {{-- LEFT --}}
                <div>

                    <h3 class="font-semibold text-slate-900 flex items-center gap-2">

                        {{-- ICON --}}
                        <span class="w-7 h-7 rounded-lg bg-white border border-[#00A39D]/10 flex items-center justify-center">
                            <x-heroicon-o-clipboard-document-list class="w-4 h-4 text-[#00A39D]" />
                        </span>

                        Riwayat Pengajuan
                    </h3>

                    <p class="text-xs text-slate-500 mt-1">
                        Data izin santri yang sudah diajukan
                    </p>

                </div>

                {{-- RIGHT BADGE --}}
                <div class="px-3 py-1 rounded-full bg-white border border-slate-100 text-xs text-slate-500 flex items-center gap-1">

                    <x-heroicon-o-rectangle-stack class="w-3.5 h-3.5 text-slate-400" />

                    {{ $perizinans->count() }} data

                </div>

            </div>

        </div>

        {{-- LIST RIWAYAT PERIZINAN --}}
        @forelse($perizinans as $index => $izin)

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

            {{-- LEFT --}}
            <div class="flex items-start gap-3">

                {{-- ICON --}}
                <div class="w-10 h-10 rounded-xl bg-[#00A39D]/10 flex items-center justify-center shrink-0">

                    @if($izin->tipe === 'pulang')
                        <x-heroicon-o-home class="w-5 h-5 text-[#00A39D]" />
                    @elseif($izin->tipe === 'keluar')
                        <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 text-[#00A39D]" />
                    @else
                        <x-heroicon-o-heart class="w-5 h-5 text-[#00A39D]" />
                    @endif

                </div>

                {{-- CONTENT --}}
                <div>

                    <div class="flex items-center gap-2">

                        <div class="font-semibold text-sm text-slate-900 capitalize">
                            {{ $izin->tipe }}
                        </div>

                        {{-- STATUS --}}
                        @if($izin->status == 'pending')
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                                Pending
                            </span>

                        @elseif($izin->status == 'approved')
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600">
                                Disetujui
                            </span>

                        @elseif($izin->status == 'dijemput')
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-50 text-amber-600">
                                Dijemput
                            </span>

                        @elseif($izin->status == 'selesai')
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">
                                Selesai
                            </span>

                        @else
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-red-50 text-red-500">
                                Ditolak
                            </span>
                        @endif

                    </div>

                    <div class="text-sm text-slate-500 mt-1">
                        {{ $izin->keperluan }}
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

                </div>

            </div>

            {{-- RIGHT (PENJEMPUT INFO + KETERANGAN) --}}
            <div class="text-right shrink-0 space-y-2">

                {{-- PENJEMPUT --}}
                <div class="flex items-center justify-end gap-1 text-xs text-slate-600">
                    <x-heroicon-o-user class="w-3.5 h-3.5 text-slate-400" />
                    <span class="font-medium">
                        {{ $izin->penjemput ?? '-' }}
                    </span>
                </div>

                {{-- NO WA --}}
                <div class="flex items-center justify-end gap-1 text-xs text-slate-400">
                    <x-heroicon-o-phone class="w-3.5 h-3.5" />
                    <span>
                        {{ $izin->no_wa ?? '-' }}
                    </span>
                </div>

                {{-- KETERANGAN WAKTU --}}
                @php
                    $keterangan = $izin->keterangan_waktu;

                    $config = [
                        'tepat_waktu' => [
                            'label' => 'Tepat Waktu',
                            'class' => 'bg-emerald-50 text-emerald-600',
                            'icon' => 'heroicon-o-check-circle'
                        ],
                        'terlambat' => [
                            'label' => 'Terlambat',
                            'class' => 'bg-yellow-50 text-yellow-600',
                            'icon' => 'heroicon-o-exclamation-circle'
                        ],
                        'sangat_terlambat' => [
                            'label' => 'Sangat Terlambat',
                            'class' => 'bg-red-50 text-red-500',
                            'icon' => 'heroicon-o-x-circle'
                        ],
                    ];

                    $k = $config[$keterangan] ?? null;
                @endphp

                @if($k)
                    <div class="flex items-center justify-end gap-1 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium {{ $k['class'] }}">
                            <x-dynamic-component :component="$k['icon']" class="w-3 h-3" />
                            {{ $k['label'] }}
                        </span>
                    </div>
                @endif

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
                Semua pengajuan izin akan tampil di sini
            </div>

        </div>

        @endforelse

        </div>

        {{-- SHOW ALL --}}
        @if($perizinans->count() > 3)
            <button @click="showAll = !showAll"
                class="w-full py-3 text-sm text-[#00A39D] font-medium">
                {{ showAll ? 'Tampilkan Sedikit' : 'Tampilkan Semua' }}
            </button>
        @endif

    </div>

</div>

@endsection