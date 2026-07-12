<!DOCTYPE html>
@php
    $pegawai = $payroll->pegawai;
    $lembaga = $pegawai?->lembagas?->first();
    $yayasan = $lembaga?->yayasan;

    $bonus = collect($payroll->adjustments ?? [])
        ->where('tipe', 'tambahan');

    $potongan = collect($payroll->adjustments ?? [])
        ->where('tipe', 'potongan');
@endphp

<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $payroll->kode }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>

    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }
    
    body{
        font-family:'Plus Jakarta Sans',sans-serif;
        width:58mm;
        margin:auto;
        color:#000;
        font-size:8.5px;
        line-height:1.35;
        padding:5px;
    }
    
    .center{
        text-align:center;
    }
    
    .logo{
        width:42px;
        height:42px;
        margin:0 auto 5px;
        object-fit:contain;
    }
    
    h1{
        font-size:11px;
        font-weight:800;
        margin-bottom:2px;
        line-height:1.2;
    }
    
    h2{
        font-size:9px;
        font-weight:700;
        line-height:1.2;
        margin-bottom:2px;
    }
    
    .divider{
        border-top:1px dashed #000;
        margin:6px 0;
    }
    
    table{
        width:100%;
        border-collapse:collapse;
    }
    
    td{
        vertical-align:top;
        padding:1px 0;
        font-size:8px;
    }
    
    td:first-child{
        width:42%;
        font-weight:500;
    }
    
    td:last-child{
        text-align:right;
        font-weight:700;
    }
    
    .section-title{
        margin:5px 0 3px;
        font-size:9px;
        font-weight:800;
        text-transform:uppercase;
    }
    
    .total{
        margin-top:3px;
        font-size:15px;
        font-weight:800;
        line-height:1.2;
    }
    
    .terbilang{
        border:1px dashed #000;
        padding:5px;
        margin-top:6px;
        font-size:7.5px;
        text-align:center;
        font-weight:600;
        line-height:1.35;
    }
    
    .footer{
        margin-top:10px;
        text-align:center;
        font-size:7.5px;
        line-height:1.4;
    }
    
    .small{
        font-size:7px;
        color:#666;
    }
    
    @media print{
    
        @page{
            size:58mm auto;
            margin:0;
        }
    
        body{
            width:58mm;
            margin:0;
        }
    
    }
    
    </style>
</head>

<body>

    <div class="center">

        @if($lembaga?->logo)
            <img src="{{ asset('storage/'.$lembaga->logo) }}" class="logo">
        @elseif($yayasan?->logo)
            <img src="{{ asset('storage/'.$yayasan->logo) }}" class="logo">
        @endif

        <h1>{{ strtoupper($yayasan?->nama) }}</h1>
        <h2>{{ strtoupper($lembaga?->nama) }}</h2>

        @if($yayasan?->alamat)
            <div style="font-size:11px">
                {{ $yayasan->alamat }}
            </div>
        @endif

        @if($yayasan?->telepon)
            <div style="font-size:11px">
                Telp. {{ $yayasan->telepon }}
            </div>
        @endif

    </div>

    <div class="divider"></div>

    <div class="center">
        <strong>SLIP GAJI</strong>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Periode</td>
            <td>
                {{ \Carbon\Carbon::create()->month($payroll->bulan)->translatedFormat('F') }}
                {{ $payroll->tahun }}
            </td>
        </tr>
        <tr>
            <td>Tanggal Bayar</td>
            <td>{{ optional($payroll->tanggal_bayar)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>{{ strtoupper($payroll->status) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Nama</td>
            <td>{{ $pegawai->nama }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $lembaga?->pivot?->jabatan ?? '-' }}</td>
        </tr>

        @if($pegawai->nip)
            <tr>
                <td>NIP</td>
                <td>{{ $pegawai->nip }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

{{-- ========================= --}}
{{-- PENDAPATAN --}}
{{-- ========================= --}}

<div class="section-title">
    PENDAPATAN
</div>

<table>

    @forelse($payroll->items as $item)

        <tr>
            <td>{{ $item->nama_komponen }}</td>
            <td>
                Rp {{ number_format($item->subtotal,0,',','.') }}
            </td>
        </tr>

        @if(($item->qty ?? 1) > 1)

            <tr>
                <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                    {{ $item->qty }} × Rp {{ number_format($item->tarif,0,',','.') }}
                </td>
            </tr>

        @endif

    @empty

        <tr>
            <td colspan="2" style="text-align:center;color:#666;">
                Tidak ada komponen pendapatan.
            </td>
        </tr>

    @endforelse

    <tr>
        <td style="padding-top:6px;">
            <strong>Subtotal Pendapatan</strong>
        </td>

        <td style="padding-top:6px;">
            <strong>
                Rp {{ number_format($payroll->subtotal ?? 0,0,',','.') }}
            </strong>
        </td>
    </tr>

</table>

<div class="divider"></div>

{{-- ========================= --}}
{{-- BONUS --}}
{{-- ========================= --}}

@if($bonus->count())

    <div class="section-title">
        BONUS
    </div>

    <table>

        @foreach($bonus as $item)

            <tr>
                <td>{{ $item->nama_komponen }}</td>
                <td>
                    + Rp {{ number_format($item->subtotal,0,',','.') }}
                </td>
            </tr>

            @if(($item->qty ?? 1) > 1)

                <tr>
                    <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                        {{ $item->qty }} × Rp {{ number_format($item->nominal ?? $item->subtotal,0,',','.') }}
                    </td>
                </tr>

            @endif

            @if(!empty($item->catatan))

                <tr>
                    <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                        {{ $item->catatan }}
                    </td>
                </tr>

            @endif

        @endforeach

        <tr>
            <td style="padding-top:6px;">
                <strong>Subtotal Bonus</strong>
            </td>

            <td style="padding-top:6px;">
                <strong>
                    + Rp {{ number_format($bonus->sum('subtotal'),0,',','.') }}
                </strong>
            </td>
        </tr>

    </table>

    <div class="divider"></div>

@endif

{{-- ========================= --}}
{{-- POTONGAN --}}
{{-- ========================= --}}

@if($potongan->count())

    <div class="section-title">
        POTONGAN
    </div>

    <table>

        @foreach($potongan as $item)

            <tr>
                <td>{{ $item->nama_komponen }}</td>
                <td>
                    - Rp {{ number_format($item->subtotal,0,',','.') }}
                </td>
            </tr>

            @if(($item->qty ?? 1) > 1)

                <tr>
                    <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                        {{ $item->qty }} × Rp {{ number_format($item->nominal ?? $item->subtotal,0,',','.') }}
                    </td>
                </tr>

            @endif

            @if(!empty($item->catatan))

                <tr>
                    <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                        {{ $item->catatan }}
                    </td>
                </tr>

            @endif

        @endforeach

        <tr>
            <td style="padding-top:6px;">
                <strong>Subtotal Potongan</strong>
            </td>

            <td style="padding-top:6px;">
                <strong>
                    - Rp {{ number_format($potongan->sum('subtotal'),0,',','.') }}
                </strong>
            </td>
        </tr>

    </table>

    <div class="divider"></div>

@endif

{{-- ========================= --}}
{{-- RINGKASAN PAYROLL --}}
{{-- ========================= --}}

<div class="section-title">
    RINGKASAN
</div>

<table>

    <tr>
        <td>Pendapatan</td>
        <td>
            Rp {{ number_format($payroll->subtotal ?? 0,0,',','.') }}
        </td>
    </tr>

    <tr>
        <td>Bonus</td>
        <td>
            + Rp {{ number_format($bonus->sum('subtotal'),0,',','.') }}
        </td>
    </tr>

    <tr>
        <td>Potongan</td>
        <td>
            - Rp {{ number_format($potongan->sum('subtotal'),0,',','.') }}
        </td>
    </tr>

</table>

<div class="divider"></div>

{{-- ========================= --}}
{{-- TOTAL DITERIMA --}}
{{-- ========================= --}}

<div class="center">

    <div style="font-size:12px;font-weight:600;">
        TOTAL
    </div>

    <div class="total">
        Rp {{ number_format($payroll->total_gaji ?? 0,0,',','.') }}
    </div>

</div>

<div class="terbilang">

    {{ terbilang($payroll->total_gaji ?? 0) }}

</div>

<div class="divider"></div>

{{-- ========================= --}}
{{-- FOOTER --}}
{{-- ========================= --}}

<div class="footer">

    <strong>Slip Gaji Resmi</strong><br>

    Gaji telah dibayarkan.

    <br><br>

    <span class="small">
        Dicetak {{ now()->translatedFormat('d/m/Y H:i') }}
    </span>

    <br>

    <span class="small">
        Powered by <strong>SantriOne</strong>
    </span>

</div>

<script>

window.onload = function () {
    window.print();
};

</script>

</body>
</html>