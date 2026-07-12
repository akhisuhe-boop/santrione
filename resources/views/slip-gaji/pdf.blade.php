<!DOCTYPE html>
<html lang="id">

@php

    $pegawai = $payroll->pegawai;
    $lembaga = $pegawai?->lembagas?->first();
    $yayasan = $lembaga?->yayasan;

@endphp

<head>

<meta charset="UTF-8">

<title>
    Slip Gaji {{ $payroll->kode }}
</title>

<style>

@page{
    margin:8mm;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    margin:0;
    padding:0;
    font-family: DejaVu Sans, sans-serif;
    color:#000;
    font-size:12px;
    line-height:1.2;

}

.wrapper{

    width:85%;
    margin:0 auto;

}

.center{
    text-align:center;
}

.logo{

    width:65px;
    height:65px;
    margin:0 auto 8px;
    object-fit:contain;

}

h1{

    font-size:16px;
    font-weight:bold;
    margin-bottom:2px;

}

h2{

    font-size:13px;
    font-weight:bold;

}

.divider{

    border-top:1px dashed #000;
    margin:10px 0;

}

table{

    width:100%;
    border-collapse:collapse;

}

td{

    vertical-align:top;
    padding:2px 0;

}

td:first-child{

    width:38%;

}

td:last-child{

    text-align:right;

}

.total{

    font-size:20px;
    font-weight:bold;
    margin-top:4px;

}

.terbilang{

    border:1px dashed #000;
    padding:8px;
    margin-top:8px;
    text-align:center;
    font-size:11px;
    font-weight:bold;
    line-height:1.5;

}

.footer{

    margin-top:10px;
    text-align:center;

}

.footer-title{

    margin-bottom:2px;
    font-size:11px;
    font-weight:bold;

}

.footer-text{

    margin-bottom:5px;
    line-height:1.2;

}

.footer-text:last-child{

    margin-bottom:0;

}

.small{

    font-size:9px;
    color:#666;
    line-height:1.15;

}

.section-title{

    margin:8px 0 4px;
    font-weight:bold;
    font-size:12px;

}

</style>

</head>

<body>

<div class="wrapper">

    {{-- HEADER --}}

    <div class="center">

        @if($lembaga?->logo)

            <img
                src="{{ public_path('storage/'.$lembaga->logo) }}"
                class="logo">

        @elseif($yayasan?->logo)

            <img
                src="{{ public_path('storage/'.$yayasan->logo) }}"
                class="logo">

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

        <strong>
            SLIP GAJI PEGAWAI
        </strong>

    </div>

    <div class="divider"></div>

    {{-- IDENTITAS --}}

    <table>

        <tr>
            <td>Periode</td>
            <td>
                {{ \Carbon\Carbon::create()->month($payroll->bulan)->translatedFormat('F') }}
                {{ $payroll->tahun }}
            </td>
        </tr>

        <tr>
            <td>Tanggal Dibayar</td>
            <td>
                {{ optional($payroll->tanggal_bayar)->translatedFormat('d F Y') }}
            </td>
        </tr>

        <tr>
            <td>Status</td>
            <td>{{ strtoupper($payroll->status) }}</td>
        </tr>

    </table>

    <div class="divider"></div>

    {{-- PEGAWAI --}}

    <table>

        <tr>
            <td>Nama Pegawai</td>
            <td>{{ $pegawai->nama }}</td>
        </tr>

        <tr>
            <td>Jabatan</td>
            <td>{{ $lembaga?->pivot?->jabatan ?? '-' }}</td>
        </tr>

        @if(!empty($pegawai->nip))

        <tr>
            <td>NIP</td>
            <td>{{ $pegawai->nip }}</td>
        </tr>

        @endif

    </table>

    <div class="divider"></div>

    {{-- PENDAPATAN --}}

    <div class="section-title">
        PENDAPATAN
    </div>

    <table>

        @foreach($payroll->items as $item)

        <tr>

            <td>{{ $item->nama_komponen }}</td>

            <td>
                Rp {{ number_format($item->subtotal,0,',','.') }}
            </td>

        </tr>

        @endforeach

    </table>

    <div class="divider"></div>

    {{-- BONUS --}}

    @if(($payroll->bonus ?? 0) > 0)

    <table>

        <tr>

            <td>Bonus</td>

            <td>
                Rp {{ number_format($payroll->bonus,0,',','.') }}
            </td>

        </tr>

    </table>

    <div class="divider"></div>

    @endif

    {{-- POTONGAN --}}

    @if(($payroll->potongan ?? 0) > 0)

    <table>

        <tr>

            <td>Potongan</td>

            <td>
                Rp {{ number_format($payroll->potongan,0,',','.') }}
            </td>

        </tr>

    </table>

    <div class="divider"></div>

    @endif

    {{-- TOTAL --}}

    <div class="center">

        TOTAL GAJI

        <div class="total">

            Rp {{ number_format($payroll->total_gaji,0,',','.') }}

        </div>

    </div>

    <div class="terbilang">

        {{ terbilang($payroll->total_gaji) }}

    </div>

    <div class="divider"></div>

    {{-- FOOTER --}}

    <div class="footer">

        <div class="footer-title">
            <strong>Slip Gaji Resmi</strong>
        </div>

        <div class="footer-text">
            Gaji telah dibayarkan kepada pegawai sesuai periode payroll.
        </div>

        <div class="footer-text small">
            Dokumen ini merupakan slip gaji resmi
            <br>
            dan diterbitkan secara otomatis oleh sistem.
        </div>

        <div class="footer-text small">
            Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}
        </div>

        <div class="footer-text small">
            Powered by <strong>SantriOne</strong> • Qinara Indonesia
        </div>

    </div>

</div>

</body>
</html>