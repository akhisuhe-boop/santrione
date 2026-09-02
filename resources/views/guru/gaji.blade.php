@extends('guru.layout.guru')

@section('content')

<div class="p-4 bg-slate-50 min-h-screen">

@php
    $slipList = $slipList ?? collect();
@endphp

{{-- Bisa 1 slip (payroll gabungan, atau cuma struktural/fungsional
     saja) atau 2 slip sekaligus (struktural + fungsional terpisah,
     buat pegawai yang rangkap jabatan). Kalau belum ada payroll sama
     sekali bulan ini, tampil 1 slip kosong (state "Belum Ada
     Pendapatan" dst, seperti sebelumnya). --}}
@forelse ($slipList as $slip)

    @include('guru.partials.slip-gaji', [
        'pegawai' => $pegawai,
        'payroll' => $slip['payroll'],
        'labelJenis' => $slip['label'],
        'riwayatMengajar' => $slip['riwayatMengajar'] ?? collect(),
    ])

@empty

    @include('guru.partials.slip-gaji', [
        'pegawai' => $pegawai,
        'payroll' => null,
        'labelJenis' => null,
        'riwayatMengajar' => collect(),
    ])

@endforelse

{{-- ================= RIWAYAT GAJI ================= --}}
@php
    $riwayatPayroll = $riwayatPayroll ?? collect();
@endphp

<div
    x-data="{ showAllPayroll:false }"
    class="bg-white
           border
           border-slate-200
           rounded-3xl
           overflow-hidden
           shadow-sm
           mb-6">

    {{-- HEADER --}}
    <div
        class="px-5 py-3
               border-b
               border-slate-100
               bg-slate-50">

        <div class="flex items-center justify-between">

            <div>

                <div class="text-base font-semibold text-slate-900">
                    Riwayat Gaji
                </div>

                <div class="text-[13px] text-slate-500 mt-1">
                    Riwayat payroll yang pernah diterima
                </div>

            </div>

            <div
                class="px-3 py-1
                       rounded-xl
                       bg-white
                       border border-slate-200
                       text-xs text-slate-600">

                {{ $riwayatPayroll->count() }} Payroll

            </div>

        </div>

    </div>

    {{-- LIST --}}
    @forelse($riwayatPayroll as $index => $item)
    
        @php
    
            $statusColor = match($item->status){
    
                'dibayar'
                    => 'bg-emerald-50 text-emerald-600',
    
                'disetujui'
                    => 'bg-blue-50 text-blue-600',
    
                'draft'
                    => 'bg-amber-50 text-amber-600',
    
                'cancelled'
                    => 'bg-red-50 text-red-600',
    
                default
                    => 'bg-slate-100 text-slate-600',
    
            };
    
            $statusLabel = match($item->status){
    
                'dibayar'
                    => 'DIBAYAR',
    
                'disetujui'
                    => 'DISETUJUI',
    
                'draft'
                    => 'DRAFT',
    
                'cancelled'
                    => 'DIBATALKAN',
    
                default
                    => strtoupper($item->status),
    
            };

            $jenisLabel = match($item->jenis){
                'struktural' => 'Struktural',
                'fungsional' => 'Fungsional',
                default => null,
            };
    
        @endphp
    
        <div
            x-show="showAllPayroll || {{ $index }} < 5"
            x-transition.duration.200ms
            class="
                px-4 py-3
                hover:bg-slate-50
                transition
                {{ !$loop->last ? 'border-b border-slate-100' : '' }}
            ">
    
            <div class="flex items-center justify-between">
    
                <div class="flex gap-3">
    
                    {{-- ICON --}}
                    <div
                        class="
                            w-10 h-10
                            rounded-xl
                            bg-[#00A39D]/10
                            flex
                            items-center
                            justify-center
                            shrink-0
                        ">
    
                        <x-heroicon-o-banknotes
                            class="w-5 h-5 text-[#00A39D]" />
    
                    </div>
    
                    {{-- CONTENT --}}
                    <div>
    
                        {{-- PERIODE --}}
                        <div
                            class="
                                font-semibold
                                text-sm
                                text-slate-900
                            ">
    
                            {{
                                \Carbon\Carbon::create()
                                    ->month($item->bulan)
                                    ->translatedFormat('F')
                            }}
                            {{ $item->tahun }}
                            @if ($jenisLabel)
                                <span class="text-xs font-normal text-slate-400">&middot; {{ $jenisLabel }}</span>
                            @endif
    
                        </div>
    
                        {{-- TOTAL --}}
                        <div
                            class="
                                text-sm
                                text-slate-500
                                mt-1
                            ">
    
                            Total Gaji
    
                            <span class="font-semibold text-slate-700">
    
                                Rp {{ number_format($item->total_gaji,0,',','.') }}
    
                            </span>
    
                        </div>
    
                        {{-- TANGGAL / STATUS --}}
                        <div
                            class="
                                flex
                                items-center
                                gap-1
                                text-xs
                                text-slate-400
                                mt-2
                            ">
    
                            <x-heroicon-o-calendar class="w-3 h-3"/>
    
                            @switch($item->status)
    
                                @case('dibayar')
    
                                    Dibayar
    
                                    @if($item->tanggal_bayar)
    
                                        •
                                        {{
                                            \Carbon\Carbon::parse($item->tanggal_bayar)
                                                ->translatedFormat('d F Y')
                                        }}
    
                                    @endif
    
                                    @break
    
                                @case('disetujui')
    
                                    Menunggu Pembayaran
    
                                    @break
    
                                @case('draft')
    
                                    Belum Dibayarkan
    
                                    @break
    
                                @case('cancelled')
    
                                    Payroll Dibatalkan
    
                                    @break
    
                                @default
    
                                    -
    
                            @endswitch
    
                        </div>
    
                    </div>
    
                </div>
    
                {{-- BADGE --}}
                <div>
    
                    <span
                        class="
                            px-3
                            py-1
                            rounded-xl
                            text-xs
                            font-semibold
                            {{ $statusColor }}
                        ">
    
                        {{ $statusLabel }}
    
                    </span>
    
                </div>
    
            </div>
    
        </div>
    
    @empty
    
        <div class="p-10">
    
            <div class="text-center">
    
                <div
                    class="
                        w-16 h-16
                        rounded-3xl
                        bg-[#00A39D]/10
                        mx-auto
                        flex
                        items-center
                        justify-center
                    ">
    
                    <x-heroicon-o-banknotes
                        class="w-8 h-8 text-[#00A39D]"/>
    
                </div>
    
                <div
                    class="
                        font-bold
                        text-slate-900
                        mt-4
                    ">
    
                    Belum Ada Riwayat Gaji
    
                </div>
    
                <div
                    class="
                        text-sm
                        text-slate-500
                        mt-2
                    ">
    
                    Riwayat pembayaran gaji akan muncul di sini.
    
                </div>
    
            </div>
    
        </div>
    
    @endforelse

    @if($riwayatPayroll->count() > 5)

        <div
            class="
                p-4
                border-t
                border-slate-100
                bg-slate-50/50
            ">

            <button
                x-on:click="
                    showAllPayroll =
                    !showAllPayroll
                "
                class="
                    w-full
                    py-3
                    rounded-2xl
                    bg-[#00A39D]/10
                    hover:bg-[#00A39D]/20
                    text-[#00A39D]
                    font-medium
                    text-sm
                    transition
                ">

                <span x-show="!showAllPayroll">
                    Lihat Semua Riwayat Gaji
                </span>

                <span x-show="showAllPayroll">
                    Tampilkan Lebih Sedikit
                </span>

            </button>

        </div>

    @endif

</div>
    
    </div>

@endsection
