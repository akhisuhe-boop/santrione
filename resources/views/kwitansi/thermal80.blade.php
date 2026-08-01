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
            width:80mm;
            margin:auto;
            color:#000;
            font-size:12px;
            line-height:1.5;
            padding:8px;

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
            font-weight:800;
            margin-bottom:2px;

        }

        h2{

            font-size:13px;
            font-weight:700;

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
            width:35%;

        }

        td:last-child{
            text-align:right;

        }

        .total{

            font-size:18px;
            font-weight:800;

        }

        .terbilang{

            border:1px dashed #000;
            padding:8px;
            margin-top:8px;
            font-size:11px;
            text-align:center;
            font-weight:600;

        }

        .footer{

            margin-top:15px;
            text-align:center;
            font-size:11px;

        }

        @media print{
            @page{
                size:80mm auto;
                margin:0;
            }

            body{

                width:80mm;
                margin:0;

            }
        }

    </style>

</head>

<body>

    {{-- Logo nanti otomatis --}}
    
    <div class="center">

    @if($lembaga?->logo)
        <img src="{{ Storage::disk('r2-public')->url($lembaga->logo) }}" class="logo">
    @elseif($yayasan?->logo)
        <img src="{{ Storage::disk('r2-public')->url($yayasan->logo) }}" class="logo">
    @endif

    <h1>{{ strtoupper($yayasan?->nama) }}</h1>

    <h2>{{ strtoupper($lembaga?->nama) }}</h2>

    @if($yayasan?->alamat)
        <div style="font-size:11px;">
            {{ $yayasan->alamat }}
        </div>
    @endif

    @if($yayasan?->telepon)
        <div style="font-size:11px;">
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
        Pembayaran telah berhasil diterima.
        <br><br>
    
        <span style="font-size:10px;color:#555;">
            Simpan kwitansi ini sebagai bukti pembayaran yang sah.
        </span>
    
        <br><br>
    
        <span style="font-size:10px;color:#777;">
            Dicetak pada {{ now()->locale('id')->translatedFormat('d F Y H:i') }}
        </span>
    
        <br>
    
        <span style="font-size:10px;color:#777;">
            Powered by <strong>QinaraApps</strong> &nbsp;•&nbsp; Qinara Indonesia
        </span>
    
    </div>
    
    <script>
        window.onload=function(){
            window.print();
        }
    </script>

</body>

</html>