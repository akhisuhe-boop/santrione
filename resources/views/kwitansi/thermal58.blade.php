<!DOCTYPE html>

@php
    $lembaga = $pembayaran->siswa?->lembaga
        ?? $pembayaran->ppdb?->lembaga;

    $yayasan = $lembaga?->yayasan;

    $tagihan = $pembayaran->tagihan;

    $nominal = $tagihan->nominal ?? 0;
    $terbayar = $tagihan->nominal_terbayar ?? 0;
    $sisa = max($nominal - $terbayar, 0);

    if ($sisa <= 0) {
        $status = 'LUNAS';
    } else {
        $status = 'BELUM LUNAS';
    }
@endphp

<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $pembayaran->kode }}</title>

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
            font-size:9px;
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
            padding:2px 0;
            font-size:8.5px;
        
        }
        
        td:first-child{
        
            width:44%;
        
        }
        
        td:last-child{
        
            text-align:right;
            font-weight:700;
        
        }
        
        .total{
        
            margin-top:3px;
            font-size:15px;
            font-weight:800;
        
        }
        
        .terbilang{
        
            border:1px dashed #000;
            padding:6px;
            margin-top:6px;
            font-size:8px;
            text-align:center;
            font-weight:600;
            line-height:1.4;
        
        }
        
        .footer{
        
            margin-top:10px;
            text-align:center;
            font-size:8px;
            line-height:1.45;
        
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

    {{-- Logo nanti otomatis --}}
    
    <div class="center">

    @if($lembaga?->logo)
        <img src="{{ asset('storage/'.$lembaga->logo) }}" class="logo">
    @elseif($yayasan?->logo)
        <img src="{{ asset('storage/'.$yayasan->logo) }}" class="logo">
    @endif

    <h1>{{ strtoupper($yayasan?->nama) }}</h1>

    <h2>{{ strtoupper($lembaga?->nama) }}</h2>

    @if($yayasan?->alamat)
        <div style="font-size:9px;">
            {{ $yayasan->alamat }}
        </div>
    @endif

    @if($yayasan?->telepon)
        <div style="font-size:9px;">
            Telp. {{ $yayasan->telepon }}
        </div>
    @endif

    </div>
    <div class="divider"></div>
    <div class="center">

        <strong>KWITANSI PEMBAYARAN</strong>

    </div>
    <div class="divider"></div>
    <table>

        <tr>
            <td>No Kwitansi</td>
            <td>{{ $pembayaran->kode }}</td>
        </tr>

        <tr>
            <td>Tanggal</td>
            <td>{{ optional($pembayaran->tanggal_bayar)->format('d/m/Y H:i') }}</td>
        </tr>

        <tr>
            <td>Metode</td>
            <td>{{ ucfirst($pembayaran->metode) }}</td>
        </tr>

    </table>
    <div class="divider"></div>
    <table>

        <tr>
            <td>Nama</td>
            <td>{{ $pembayaran->siswa?->nama_lengkap ?? $pembayaran->ppdb?->nama_lengkap }}</td>
        </tr>

        <tr>
            <td>Tagihan</td>
            <td>{{ $pembayaran->tagihan->judul }}</td>
        </tr>

        <tr>
    <td>Total Tagihan</td>
    <td>
        Rp {{ number_format($tagihan->nominal,0,',','.') }}
    </td>
    </tr>
    
    <tr>
        <td>Sudah Dibayar</td>
        <td>
            Rp {{ number_format($tagihan->nominal_terbayar,0,',','.') }}
        </td>
    </tr>
    
    <tr>
        <td>Sisa Tagihan</td>
        <td>
            Rp {{ number_format($sisa,0,',','.') }}
        </td>
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

        <strong>Terima Kasih</strong><br>
    
        Pembayaran telah diterima.
    
        <br><br>
    
        <span class="small">
            Simpan kwitansi ini sebagai bukti pembayaran.
        </span>
    
        <br><br>
    
        <span class="small">
            Dicetak {{ now()->format('d/m/Y H:i') }}
        </span>
    
        <br>
    
        <span class="small">
            Powered by <strong>QianraApps</strong> &nbsp;•&nbsp; Qinara Indonesia
        </span>
    
    </div>
    
    <script>
        window.onload=function(){
            window.print();
        }
    </script>

</body>

</html>