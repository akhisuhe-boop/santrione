@extends('wali.layout.wali')

@section('content')

<div class="p-4 bg-[#F7F9FC] min-h-screen">

    {{-- NOTIFIKASI --}}
    @if(session('success'))
    <div
        x-data="{ show:true }"
        x-init="setTimeout(() => show=false,4000)"
        x-show="show"
        x-transition
        class="mb-4 rounded-[24px]
            border border-green-200
            bg-green-50
            p-4">

        <div class="flex items-center gap-4">

            <div
                class="w-12 h-12 rounded-full
                    bg-green-100
                    flex items-center justify-center">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-green-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M5 13l4 4L19 7" />

                </svg>

            </div>

            <div>

                <div class="font-semibold text-[16px] text-green-700">
                    Pembayaran Berhasil
                </div>

                <div class="text-[14px] text-green-600">
                    {{ session('success') }}
                </div>

            </div>

        </div>

    </div>

    @endif

    @if(session('error'))
    <div
        x-data="{ show:true }"
        x-init="setTimeout(() => show=false,4000)"
        x-show="show"
        x-transition
        class="mb-4 rounded-[24px]
            border border-red-200
            bg-red-50
            p-4">

        <div class="flex items-center gap-4">

            <div
                class="w-12 h-12 rounded-full
                    bg-red-100
                    flex items-center justify-center">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-red-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12A9 9 0 1112 3a9 9 0 019 9z"/>

                </svg>

            </div>

            <div>

                <div class="font-semibold text-[16px] text-red-700">
                    Pembayaran Gagal
                </div>

                <div class="text-[14px] text-red-600">
                    {{ session('error') }}
                </div>

            </div>

        </div>

    </div>

    @endif

    @php

        $saldo = $wallet->saldo ?? 0;

        $jumlahTagihan = $tagihanAktif->count();

        $jumlahLunas = $riwayatPembayaran->count();

        $totalTahunan = $siswa->tagihans->sum('nominal');

    @endphp

    {{-- HEADER --}}
    <div class="mb-4">

        <h1 class="font-bold text-[18px] text-slate-900">
            Keuangan
        </h1>

        <p class="text-[12px] text-slate-500">
            Kelola pembayaran & tagihan
        </p>

    </div>

    {{-- SALDO --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 to-teal-500 text-white p-6 shadow-lg mb-6">

        {{-- bubble dekorasi --}}
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full"></div>
        <div class="absolute bottom-0 right-0 w-32 h-32 bg-white/10 rounded-full"></div>

        <div class="flex items-center gap-2 text-white/80 text-sm">
            <x-heroicon-o-credit-card class="w-5 h-5" />
            <span>Saldo Aktif</span>
        </div>

        <h1 class="text-3xl font-bold mt-2">
            Rp {{ number_format($wallet->saldo ?? 0,0,',','.') }}
        </h1>

        <div class="text-white/80 text-xs mt-2">
            {{ $siswa->nama_lengkap }}
        </div>

    </div>

    {{-- TAGIHAN --}}
    <div class="bg-white rounded-[28px] shadow-sm p-4 mt-5">

        <div class="flex justify-between items-center mb-4">

            <h3 class="font-bold text-[16px] text-slate-900">
                Tagihan Aktif
            </h3>

            <span
                class="bg-red-100 text-red-500 px-3 py-1 rounded-full text-[11px] font-semibold">
                {{ $jumlahTagihan }} Tagihan
            </span>

        </div>

        @forelse($tagihanAktif as $tagihan)

            @php
            $status = strtolower(trim($tagihan->status));
            $pembayaranDitolak = $tagihan->pembayarans
                ->where('status', 'gagal')
                ->sortByDesc('created_at')
                ->first();
            switch ($status) {

                case 'sebagian':

                    $cardClass = 'bg-yellow-50 border-yellow-200';
                    $iconClass = 'bg-yellow-100 text-yellow-600';
                    $badgeClass = 'bg-yellow-100 text-yellow-700';
                    $buttonClass = 'bg-yellow-500 hover:bg-yellow-600';

                    break;

                case 'belum':

                    $cardClass = 'bg-red-50 border-red-200';
                    $iconClass = 'bg-red-100 text-red-500';
                    $badgeClass = 'bg-red-100 text-red-500';
                    $buttonClass = 'bg-red-500 hover:bg-red-600';

                    break;

                default:

                    $cardClass = 'bg-slate-50 border-slate-200';
                    $iconClass = 'bg-slate-100 text-slate-500';
                    $badgeClass = 'bg-slate-100 text-slate-500';
                    $buttonClass = 'bg-slate-500';

                    break;
            }

            @endphp

            <div
                class="rounded-[22px] border p-4 mb-4 {{ $cardClass }}">

                <div class="flex justify-between gap-3">

                    <div class="flex gap-3 flex-1">

                        <div
                            class="w-11 h-11 rounded-2xl flex items-center justify-center {{ $iconClass }}">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke-width="2"
                                 stroke="currentColor"
                                 class="w-4 h-4">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>

                        </div>

                        <div>

                            <div class="font-semibold text-[15px] text-slate-900">
                                {{ $tagihan->judul }}
                            </div>

                            <div class="text-[12px] text-slate-500 mt-1">
                                Jatuh Tempo:
                                {{ optional($tagihan->jatuh_tempo)->format('d M Y') }}
                            </div>

                            <span
                                class="inline-flex mt-2 px-3 py-1 rounded-xl text-[11px] font-semibold {{ $badgeClass }}">
                                {{ $tagihan->status }}
                            </span>

                        </div>

                    </div>

                    <div class="text-right">

                        <div
                            class="font-bold text-[16px] text-slate-900">

                            Rp {{ number_format($tagihan->nominal,0,',','.') }}

                        </div>

                    </div>

                </div>
                    @if($pembayaranDitolak)

                    <div
                        class="
                            mt-4
                            rounded-2xl
                            border
                            border-red-200
                            bg-red-50
                            p-4
                        ">

                        <div class="flex gap-3">

                            <div
                                class="
                                    w-10 h-10
                                    rounded-xl
                                    bg-red-100
                                    flex items-center justify-center
                                    shrink-0
                                ">

                                <x-heroicon-o-exclamation-triangle
                                    class="w-5 h-5 text-red-500" />

                            </div>

                            <div>

                                <div
                                    class="
                                        font-semibold
                                        text-red-700
                                        text-sm
                                    ">

                                    Pembayaran Transfer Ditolak

                                </div>

                                <div
                                    class="
                                        text-xs
                                        text-red-600
                                        mt-1
                                        leading-relaxed
                                    ">

                                    {{ $pembayaranDitolak->keterangan }}

                                </div>

                            </div>

                        </div>

                    </div>

                    @endif
                <a
                    href="{{ route('wali.pembayaran.show', $tagihan->id) }}"
                    class="block w-full mt-3 rounded-2xl py-2.5 text-[13px] text-white font-semibold text-center {{ $buttonClass }}">

                    Bayar Sekarang

                </a>

            </div>

        @empty

            <div class="text-center py-8 text-slate-400 text-sm">
                Tidak ada tagihan aktif
            </div>

        @endforelse

    </div>

    {{-- RIWAYAT PEMBAYARAN --}}
<div class="bg-white rounded-[28px] shadow-sm p-4 mt-5 mb-8">

    <div class="flex justify-between items-center mb-4">

        <h3 class="font-bold text-[16px] text-slate-900">
            Riwayat Pembayaran
        </h3>

        @if($riwayatPembayaran->count() > 5)

            <button
                id="btnRiwayat"
                onclick="toggleRiwayat()"
                class="text-[#00A39D] text-sm font-semibold">

                Semua →

            </button>

        @endif

    </div>

    @forelse($riwayatPembayaran->take(5) as $riwayat)

        <div class="flex items-center justify-between py-3 border-b border-slate-100">

            <div class="flex items-center gap-3">

                <div
                    class="w-11 h-11 rounded-2xl bg-green-50 flex items-center justify-center">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke-width="2.5"
                         stroke="currentColor"
                         class="w-4 h-4 text-green-500">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>

                </div>

                <div>

                    <div class="font-semibold text-[15px] text-slate-900">
                        {{ $riwayat->tagihan->judul }}
                    </div>

                    <div class="text-[12px] text-slate-500">
                        {{ optional($riwayat->tanggal_bayar)->format('d M Y') }}
                    </div>

                </div>

            </div>

            <div class="text-right">

                <div class="font-bold text-[15px] text-slate-900">
                    Rp {{ number_format($riwayat->nominal,0,',','.') }}
                </div>

                <div class="flex items-center justify-end gap-2 mt-1 text-[12px]">

                    <span class="text-green-500 font-medium">
                        Lunas
                    </span>
                
                    <a
                        href="{{ route('kwitansi.pdf', $riwayat->id) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1 text-[#00A39D] font-semibold hover:text-[#00837e]">
                
                        <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5"/>
                
                        Kwitansi
                
                    </a>
                
                </div>

            </div>

        </div>

    @empty

        <div class="text-center py-8 text-slate-400 text-sm">
            Belum ada riwayat pembayaran
        </div>

    @endforelse

    @if($riwayatPembayaran->count() > 5)

        <div id="riwayatTambahan" class="hidden">

            @foreach($riwayatPembayaran->skip(5) as $riwayat)

                <div class="flex items-center justify-between py-3 border-b border-slate-100">

                    <div class="flex items-center gap-3">

                        <div
                            class="w-11 h-11 rounded-2xl bg-green-50 flex items-center justify-center">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 fill="none"
                                 viewBox="0 0 24 24"
                                 stroke-width="2.5"
                                 stroke="currentColor"
                                 class="w-4 h-4 text-green-500">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>

                        </div>

                        <div>

                            <div class="font-semibold text-[15px] text-slate-900">
                                {{ $riwayat->judul }}
                            </div>

                            <div class="text-[12px] text-slate-500">
                                {{ optional($riwayat->updated_at)->format('d M Y') }}
                            </div>

                        </div>

                    </div>

                    <div class="text-right">

                        <div class="font-bold text-[15px] text-slate-900">
                            Rp {{ number_format($riwayat->nominal,0,',','.') }}
                        </div>

                        <div class="text-[12px] text-green-500">
                            Lunas
                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>

<script>

function toggleRiwayat()
{
    const riwayat = document.getElementById('riwayatTambahan');
    const tombol = document.getElementById('btnRiwayat');

    if (riwayat.classList.contains('hidden')) {

        riwayat.classList.remove('hidden');
        tombol.innerHTML = 'Sembunyikan ↑';

    } else {

        riwayat.classList.add('hidden');
        tombol.innerHTML = 'Semua →';

    }
}

</script>

</div>

@endsection