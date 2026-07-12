<!DOCTYPE html>

@php
    $lembaga = $pembayaran->siswa?->lembaga
        ?? $pembayaran->ppdb?->lembaga;

    $yayasan = $lembaga?->yayasan;

    $tagihan = $pembayaran->tagihan;

    $nominal  = $tagihan->nominal ?? 0;
    $terbayar = $tagihan->nominal_terbayar ?? 0;
    $sisa     = max($nominal - $terbayar, 0);

    $status = $sisa <= 0
        ? 'LUNAS'
        : 'BELUM LUNAS';
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

@page{

    /* Continuous Form 9.5 x 5.5 inch */
    size:24cm 14cm landscape;
    margin:5mm;

}

html,
body{

    width:100%;
    height:100%;

}

body{

    font-family:'Plus Jakarta Sans',sans-serif;
    color:#111;
    background:#fff;

    font-size:10px;
    line-height:1.35;

}

.container{

    width:100%;
    height:100%;
    padding:7mm 10mm;

}

.center{

    text-align:center;

}

/* ===========================
   HEADER
=========================== */

.logo{

    width:42px;
    height:42px;
    object-fit:contain;
    margin-bottom:5px;

}

.yayasan{

    font-size:18px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.5px;

}

.lembaga{

    margin-top:2px;
    font-size:13px;
    font-weight:700;
    text-transform:uppercase;

}

.alamat{

    margin-top:4px;
    font-size:9px;
    line-height:1.4;
    color:#555;

}

/* ===========================
   DIVIDER
=========================== */

.divider{

    border-top:1px solid #333;
    margin:8px 0;

}

/* ===========================
   TITLE
=========================== */

.judul{

    text-align:center;
    font-size:14px;
    font-weight:800;
    letter-spacing:.8px;

}

/* ===========================
   GRID
=========================== */

.grid{

    display:flex;
    gap:14px;
    margin-top:8px;

}

.col{

    flex:1;

}

/* ===========================
   TABLE
=========================== */

table{

    width:100%;
    border-collapse:collapse;

}

td{

    padding:2px 0;
    vertical-align:top;
    font-size:10px;

}

td:first-child{

    width:78px;
    font-weight:500;

}

td:nth-child(2){

    width:10px;
    text-align:center;

}

td:last-child{

    font-weight:700;

}

/* ===========================
   TOTAL
=========================== */

.total-box{

    margin-top:10px;
    text-align:center;

}

.label-total{

    font-size:12px;
    font-weight:700;
    letter-spacing:1px;

}

.total{

    margin-top:3px;
    font-size:28px;
    font-weight:800;
    line-height:1.2;

}

/* ===========================
   TERBILANG
=========================== */

.terbilang{

    margin-top:8px;

    border:1px solid #444;

    padding:8px;

    text-align:center;

    font-size:10px;

    font-weight:600;

    border-radius:3px;

}

/* ===========================
   FOOTER
=========================== */

.footer-grid{

    display:flex;
    justify-content:space-between;
    gap:20px;
    margin-top:12px;

}

.footer-left{

    flex:1;
    font-size:9px;
    line-height:1.5;

}

.footer-right{

    width:180px;
    text-align:center;
    font-size:9px;

}

.signature{

    height:42px;

}

.powered{

    margin-top:8px;
    text-align:center;
    font-size:8px;
    color:#666;

}

@media print{

    body{

        margin:0;

        -webkit-print-color-adjust:exact;
        print-color-adjust:exact;

    }

}

</style>

</head>

<body>

<div class="container">

{{-- ===========================
     HEADER
=========================== --}}

<div class="center">

    @if($lembaga?->logo)
        <img src="{{ asset('storage/'.$lembaga->logo) }}" class="logo">
    @elseif($yayasan?->logo)
        <img src="{{ asset('storage/'.$yayasan->logo) }}" class="logo">
    @endif

    <div class="yayasan">
        {{ strtoupper($yayasan?->nama) }}
    </div>

    <div class="lembaga">
        {{ strtoupper($lembaga?->nama) }}
    </div>

    @if($yayasan?->alamat)
        <div class="alamat">

            {{ $yayasan->alamat }}

            @if($yayasan?->telepon)
                <br>
                Telp. {{ $yayasan->telepon }}
            @endif

        </div>
    @endif

</div>

<div class="divider"></div>

<div class="judul">
    KWITANSI PEMBAYARAN
</div>

<div class="divider"></div>

{{-- ===========================
     INFORMASI
=========================== --}}

<div class="grid">

    {{-- ===========================
         KOLOM KIRI
    ============================ --}}

    <div class="col">

        <table>

            <tr>
                <td>No. Kwitansi</td>
                <td>:</td>
                <td>{{ $pembayaran->kode }}</td>
            </tr>

            <tr>
                <td>Tanggal Bayar</td>
                <td>:</td>
                <td>{{ optional($pembayaran->tanggal_bayar)->translatedFormat('d F Y H:i') }}</td>
            </tr>

            <tr>
                <td>Metode</td>
                <td>:</td>
                <td>{{ ucfirst($pembayaran->metode) }}</td>
            </tr>

            <tr>
                <td>Nama</td>
                <td>:</td>
                <td>
                    {{ $pembayaran->siswa?->nama_lengkap ?? $pembayaran->ppdb?->nama_lengkap }}
                </td>
            </tr>

            @if($pembayaran->siswa)

            <tr>
                <td>NIS</td>
                <td>:</td>
                <td>{{ $pembayaran->siswa->nis }}</td>
            </tr>

            @endif

            @if($pembayaran->ppdb)

            <tr>
                <td>NISN</td>
                <td>:</td>
                <td>{{ $pembayaran->ppdb->nisn }}</td>
            </tr>

            @endif

        </table>

    </div>

    {{-- ===========================
         KOLOM KANAN
    ============================ --}}

    <div class="col">

        <table>

            <tr>
                <td>Tagihan</td>
                <td>:</td>
                <td>{{ $tagihan->judul }}</td>
            </tr>

            <tr>
                <td>Total</td>
                <td>:</td>
                <td>
                    Rp {{ number_format($nominal,0,',','.') }}
                </td>
            </tr>

            <tr>
                <td>Terbayar</td>
                <td>:</td>
                <td>
                    Rp {{ number_format($terbayar,0,',','.') }}
                </td>
            </tr>

            <tr>
                <td>Sisa</td>
                <td>:</td>
                <td>
                    Rp {{ number_format($sisa,0,',','.') }}
                </td>
            </tr>

            <tr>
                <td>Status</td>
                <td>:</td>
                <td>{{ $status }}</td>
            </tr>

        </table>

    </div>

</div>

<div class="divider"></div>

{{-- ===========================
     TOTAL PEMBAYARAN
=========================== --}}

<div class="total-box">

    <div class="label-total">
        TOTAL PEMBAYARAN
    </div>

    <div class="total">
        Rp {{ number_format($pembayaran->nominal,0,',','.') }}
    </div>

</div>

{{-- ===========================
     TERBILANG
=========================== --}}

<div class="terbilang">

    {{ terbilang($pembayaran->nominal) }}

</div>

<div class="divider"></div>

{{-- ===========================
     FOOTER
=========================== --}}

<div class="footer-grid">

    {{-- ===========================
         KETERANGAN
    ============================ --}}

    <div class="footer-left">

        <strong>Keterangan</strong>

        <br><br>

        Pembayaran ini telah diterima oleh
        {{ $yayasan?->nama }}
        sebagai pembayaran
        <strong>{{ $tagihan->judul }}</strong>.

        <br><br>

        Status pembayaran saat ini :

        <strong>{{ $status }}</strong>

        @if($sisa > 0)

            <br>

            Sisa tagihan sebesar

            <strong>
                Rp {{ number_format($sisa,0,',','.') }}
            </strong>

            masih harus diselesaikan.

        @endif

    </div>

    {{-- ===========================
         TANDA TANGAN
    ============================ --}}

    <div class="footer-right">

        {{ $yayasan?->kota ?? 'Serang' }},
        {{ now()->translatedFormat('l, d F Y') }}

        <br><br>

        Bendahara

        <div class="signature"></div>

        <strong>

            {{ $lembaga?->bendahara?->nama
                ?? $yayasan?->bendahara?->nama
                ?? '(...................................)' }}

        </strong>

    </div>

</div>

{{-- ===========================
     PENUTUP
=========================== --}}

<div class="powered">

    <strong>Terima Kasih</strong>

    &nbsp;•&nbsp;

    Dokumen ini dicetak otomatis oleh sistem.

    &nbsp;•&nbsp;

    Dicetak : {{ now()->translatedFormat('d F Y H:i') }}

    <br>

    Powered by <strong>QinaraApps</strong> &nbsp;•&nbsp; Qinara Indonesia

</div>

</div>

<script>

window.onload = function () {

    window.print();

};

</script>

</body>
</html>
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    