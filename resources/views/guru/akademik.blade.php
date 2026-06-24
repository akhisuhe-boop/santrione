@extends('wali.layout.wali')

@section('content')

<div class="p-4 space-y-4">

@php
    // =========================
    // ABSENSI REAL
    // =========================
    $hadir = $siswa->absensis->where('status','Hadir')->count();
    $totalAbsensi = $siswa->absensis->count();
    $persentaseHadir = $totalAbsensi > 0
        ? round(($hadir / $totalAbsensi) * 100)
        : 0;

    // =========================
    // AKADEMIK REAL
    // =========================
    $prestasi = $siswa->prestasiSiswa->count();
    $pelanggaran = $siswa->pelanggaranSiswa->count();

    // =========================
    // TAHFIDZ REAL (DARI SETORAN)
    // =========================
    $setoran = $siswa->tahfidzSetoran;

    $setoranTerakhir = $setoran->sortByDesc('tanggal')->first();
    $totalSetoran = $setoran->count();
    $rataNilai = $setoran->avg('nilai') ?? 0;

    // =========================
    // KEUANGAN REAL
    // =========================
    $tagihanAktif = $tagihanAktif ?? collect();
    $riwayat = $riwayatPembayaran ?? collect();

    $totalTagihan = $tagihanAktif->count();
    $totalLunas = $riwayat->count();

    $totalNominalTagihan = $siswa->tagihans->sum('nominal');

@endphp


{{-- ========================= --}}
{{-- HEADER RESUME --}}
{{-- ========================= --}}
<div class="bg-white rounded-2xl border p-4">

    <div class="flex items-center gap-3">

        <div class="w-14 h-14 rounded-2xl bg-[#00A39D] text-white flex items-center justify-center font-bold text-lg">
            {{ strtoupper(substr($siswa->nama_lengkap,0,2)) }}
        </div>

        <div class="flex-1">
            <div class="font-bold text-slate-900">
                {{ $siswa->nama_lengkap }}
            </div>

            <div class="text-xs text-slate-500">
                NIS {{ $siswa->nis }} • {{ $siswa->kelas?->nama ?? '-' }}
            </div>
        </div>

        <span class="text-xs px-3 py-1 rounded-xl bg-emerald-50 text-emerald-600 font-medium">
            {{ $siswa->status_siswa }}
        </span>

    </div>

</div>


{{-- ========================= --}}
{{-- KPI GRID REAL DATA --}}
{{-- ========================= --}}
<div class="grid grid-cols-2 gap-3">

    {{-- ABSENSI --}}
    <div class="bg-white border rounded-2xl p-3">
        <div class="text-xs text-slate-500">Kehadiran</div>
        <div class="text-xl font-bold text-[#00A39D] mt-1">
            {{ $persentaseHadir }}%
        </div>
        <div class="text-[11px] text-slate-400">
            {{ $hadir }}/{{ $totalAbsensi }} hadir
        </div>
    </div>

    {{-- TAHFIDZ --}}
    <div class="bg-white border rounded-2xl p-3">
        <div class="text-xs text-slate-500">Setoran</div>
        <div class="text-xl font-bold text-indigo-600 mt-1">
            {{ $totalSetoran }}
        </div>
        <div class="text-[11px] text-slate-400">
            Avg nilai {{ round($rataNilai,1) }}
        </div>
    </div>

    {{-- PRESTASI --}}
    <div class="bg-white border rounded-2xl p-3">
        <div class="text-xs text-slate-500">Prestasi</div>
        <div class="text-xl font-bold text-amber-600 mt-1">
            {{ $prestasi }}
        </div>
        <div class="text-[11px] text-slate-400">
            penghargaan
        </div>
    </div>

    {{-- PELANGGARAN --}}
    <div class="bg-white border rounded-2xl p-3">
        <div class="text-xs text-slate-500">Pelanggaran</div>
        <div class="text-xl font-bold text-red-500 mt-1">
            {{ $pelanggaran }}
        </div>
        <div class="text-[11px] text-slate-400">
            catatan disiplin
        </div>
    </div>

</div>


{{-- ========================= --}}
{{-- TAHFIDZ SUMMARY (REAL) --}}
{{-- ========================= --}}
@if($setoranTerakhir)

<div class="bg-white border rounded-2xl p-4">

    <div class="text-sm font-semibold text-slate-900 mb-3">
        Tahfidz Terakhir
    </div>

    <div class="flex justify-between">

        <div>
            <div class="text-xs text-slate-500">Surah</div>
            <div class="font-bold">
                {{ $setoranTerakhir->surah?->nama ?? '-' }}
            </div>
        </div>

        <div class="text-right">
            <div class="text-xs text-slate-500">Nilai</div>
            <div class="font-bold text-[#00A39D]">
                {{ $setoranTerakhir->nilai }}
            </div>
        </div>

    </div>

    <div class="text-xs text-slate-500 mt-2">
        {{ \Carbon\Carbon::parse($setoranTerakhir->tanggal)->format('d M Y') }}
    </div>

</div>

@endif


{{-- ========================= --}}
{{-- KEUANGAN REAL RESUME FIX --}}
{{-- ========================= --}}

@php
    $saldo = $wallet->saldo ?? 0;

    // pastikan collection aman
    $tagihanAktifList = collect($tagihanAktif ?? []);
    $riwayatList = collect($riwayatPembayaran ?? []);

    // TOTAL TAGIHAN AKTIF (ini sudah benar dari controller)
    $jumlahTagihanAktif = $tagihanAktifList->count();

    // LUNAS = dari riwayat pembayaran (SUDAH FINAL DATA)
    $jumlahLunas = $riwayatList->count();

    // BELUM = tagihan aktif yang belum dibayar
    // (bukan pakai status, tapi sisa data)
    $jumlahBelum = max($jumlahTagihanAktif - $jumlahLunas, 0);

    // total nominal tagihan
    $totalNominalTagihan = optional($siswa->tagihans)->sum('nominal') ?? 0;
@endphp

<div class="bg-white border rounded-2xl p-4">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-3">

        <div>
            <div class="text-sm font-semibold text-slate-900">
                Keuangan
            </div>
            <div class="text-xs text-slate-500">
                Ringkasan pembayaran santri
            </div>
        </div>

        <div class="text-right">
            <div class="text-xs text-slate-500">Saldo</div>
            <div class="font-bold text-[#00A39D]">
                Rp {{ number_format($saldo,0,',','.') }}
            </div>
        </div>

    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-3 gap-3">

        <div class="bg-red-50 border border-red-100 rounded-xl p-3">
            <div class="text-xs text-slate-500">Tagihan</div>
            <div class="text-lg font-bold text-red-500">
                {{ $jumlahTagihanAktif }}
            </div>
        </div>

        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3">
            <div class="text-xs text-slate-500">Lunas</div>
            <div class="text-lg font-bold text-emerald-600">
                {{ $jumlahLunas }}
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-100 rounded-xl p-3">
            <div class="text-xs text-slate-500">Belum</div>
            <div class="text-lg font-bold text-amber-600">
                {{ $jumlahBelum }}
            </div>
        </div>

    </div>

    {{-- TOTAL --}}
    <div class="mt-4 flex items-center justify-between">

        <div>
            <div class="text-xs text-slate-500">
                Total Tagihan Tahun Ini
            </div>
            <div class="font-bold text-slate-900">
                Rp {{ number_format($totalNominalTagihan,0,',','.') }}
            </div>
        </div>

        <a href="{{ route('wali.keuangan') }}"
           class="text-xs font-semibold text-[#00A39D]">
            Detail →
        </a>

    </div>

</div>


{{-- ========================= --}}
{{-- ALERT DISIPLIN --}}
{{-- ========================= --}}
@if($pelanggaran > 0)

<div class="bg-red-50 border border-red-200 rounded-2xl p-4">

    <div class="text-sm font-semibold text-red-600">
        Perhatian Disiplin
    </div>

    <div class="text-xs text-red-500 mt-1">
        Santri memiliki {{ $pelanggaran }} catatan pelanggaran
    </div>

</div>

@endif

</div>

@endsection