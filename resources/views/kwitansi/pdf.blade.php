<!DOCTYPE html>
<html lang="id">

@php
    $lembaga = $pembayaran->siswa?->lembaga
        ?? $pembayaran->ppdb?->lembaga;

    $yayasan = $lembaga?->yayasan;

    $tagihan = $pembayaran->tagihan;

    $nominal = $tagihan->nominal ?? 0;
    $terbayar = $tagihan->nominal_terbayar ?? 0;
    $sisa = max($nominal - $terbayar, 0);

    $status = $sisa <= 0 ? 'LUNAS' : 'BELUM LUNAS';
@endphp

<head>

    <meta charset="UTF-8">

    <title>
        {{ $pembayaran->kode }}
    </title>

    <style>

        @page{
            margin: 14mm 8mm;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
        
            margin:0;
            padding:0;
            font-family: 'Plus Jakarta Sans', sans-serif;
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
            margin:6px auto 8px;
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

            width:36%;

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

    </style>

</head>

<body>

<div class="wrapper">

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
            KWITANSI PEMBAYARAN
        </strong>

    </div>

    <div class="divider"></div>

    <table>

        <tr>
            <td>No. Kwitansi</td>
            <td>{{ $pembayaran->kode }}</td>
        </tr>

        <tr>
            <td>Tanggal</td>
            <td>{{ optional($pembayaran->tanggal_bayar)->locale('id')->translatedFormat('d F Y H:i') }}</td>
        </tr>

        <tr>
            <td>Metode</td>
            <td>{{ $pembayaran->metode === 'admin' ? 'Tunai' : ucfirst($pembayaran->metode) }}</td>
        </tr>

    </table>

    <div class="divider"></div>

    <table>

        <tr>
            <td>Nama</td>
            <td>{{ $pembayaran->siswa?->nama_lengkap ?? $pembayaran->ppdb?->nama_lengkap }}</td>
        </tr>
        @if($pembayaran->siswa)
        <tr>
            <td>Kelas</td>
            <td>{{ $pembayaran->siswa->kelas?->nama ?? '-' }}</td>
        </tr>
        @endif

        <tr>
            <td>Tagihan</td>
            <td>{{ $tagihan->judul }}</td>
        </tr>

        <tr>
            <td>Total Tagihan</td>
            <td>Rp {{ number_format($nominal,0,',','.') }}</td>
        </tr>

        <tr>
            <td>Sudah Dibayar</td>
            <td>Rp {{ number_format($terbayar,0,',','.') }}</td>
        </tr>

        <tr>
            <td>Sisa Tagihan</td>
            <td>Rp {{ number_format($sisa,0,',','.') }}</td>
        </tr>

        <tr>
            <td>Status</td>
            <td>{{ $status }}</td>
        </tr>

    </table>

    <div class="divider"></div>

    <div class="center">

        TOTAL

        <div class="total">

            Rp {{ number_format($pembayaran->nominal,0,',','.') }}

        </div>

    </div>

    <div class="terbilang">

        {{ terbilang($pembayaran->nominal) }}

    </div>

    <div class="divider"></div>

    <div class="footer">
    
        <div class="footer-title">
            <strong>Terima Kasih</strong>
        </div>
    
        <div class="footer-text">
            Pembayaran telah berhasil diterima.
        </div>
    
        <div class="footer-text small">
            Dokumen ini merupakan bukti pembayaran yang sah
            <br>
            dan diterbitkan secara otomatis oleh sistem.
        </div>
    
        <div class="footer-text small">
            Dicetak pada {{ now()->locale('id')->translatedFormat('d F Y H:i') }}
        </div>
    
        <div class="footer-text small">
            Powered by <strong>QinaraApps</strong> • Qinara Indonesia
        </div>
    
    </div>

</div>

</body>

</html>