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

@page{
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

.divider{
    border-top:1px solid #333;
    margin:8px 0;
}

.judul{
    text-align:center;
    font-size:14px;
    font-weight:800;
    letter-spacing:.8px;
}

.grid{
    display:flex;
    gap:14px;
    margin-top:8px;
}

.col{
    flex:1;
}

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
    width:92px;
    font-weight:500;
}

td:nth-child(2){
    width:10px;
    text-align:center;
}

td:last-child{
    font-weight:700;
}

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

.terbilang{
    margin-top:8px;
    border:1px solid #444;
    padding:8px;
    text-align:center;
    font-size:10px;
    font-weight:600;
    border-radius:3px;
}

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

<div class="center">

    @if($lembaga?->logo)
        <img src="{{ Storage::disk('r2-public')->url($lembaga->logo) }}" class="logo">
    @elseif($yayasan?->logo)
        <img src="{{ Storage::disk('r2-public')->url($yayasan->logo) }}" class="logo">
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
    SLIP GAJI PEGAWAI
</div>

<div class="divider"></div>

<div class="grid">

    {{-- =========================
         KOLOM KIRI
    ========================== --}}

    <div class="col">

        <table>

            <tr>
                <td>Periode</td>
                <td>:</td>
                <td>
                    {{ \Carbon\Carbon::create()->month($payroll->bulan)->translatedFormat('F') }}
                    {{ $payroll->tahun }}
                </td>
            </tr>

            <tr>
                <td>Tanggal Bayar</td>
                <td>:</td>
                <td>
                    {{ optional($payroll->tanggal_bayar)->translatedFormat('d F Y') }}
                </td>
            </tr>

            <tr>
                <td>Status</td>
                <td>:</td>
                <td>{{ strtoupper($payroll->status) }}</td>
            </tr>

            <tr>
                <td>Nama Pegawai</td>
                <td>:</td>
                <td>{{ $pegawai->nama }}</td>
            </tr>

            @if($pegawai->nip)
            <tr>
                <td>NIP</td>
                <td>:</td>
                <td>{{ $pegawai->nip }}</td>
            </tr>
            @endif

            <tr>
                <td>Jabatan</td>
                <td>:</td>
                <td>{{ $lembaga?->pivot?->jabatan ?? '-' }}</td>
            </tr>

        </table>

    </div>

    {{-- =========================
         KOLOM KANAN
         (PART 2)
    ========================== --}}
    <div class="col">

    <table>

        <tr>
            <td>Pendapatan</td>
            <td>:</td>
            <td>
                Rp {{ number_format($payroll->subtotal ?? 0,0,',','.') }}
            </td>
        </tr>

        <tr>
            <td>Bonus</td>
            <td>:</td>
            <td>
                Rp {{ number_format($bonus->sum('subtotal'),0,',','.') }}
            </td>
        </tr>

        <tr>
            <td>Potongan</td>
            <td>:</td>
            <td>
                Rp {{ number_format($potongan->sum('subtotal'),0,',','.') }}
            </td>
        </tr>

        <tr>
            <td>Total Diterima</td>
            <td>:</td>
            <td>
                Rp {{ number_format($payroll->total_gaji ?? 0,0,',','.') }}
            </td>
        </tr>

    </table>

    <div style="margin-top:10px;">

        <table>

            <tr>
                <td colspan="3" style="font-weight:700;padding-bottom:4px;">
                    Rincian Pendapatan
                </td>
            </tr>

            @foreach($payroll->items as $item)

                <tr>
                    <td colspan="2">
                        {{ $item->nama_komponen }}
                    </td>

                    <td style="text-align:right;">
                        Rp {{ number_format($item->subtotal,0,',','.') }}
                    </td>
                </tr>

            @endforeach

        </table>

    </div>

</div>

</div>

<div class="divider"></div>

{{-- ===========================
     TOTAL GAJI
=========================== --}}

<div class="total-box">

    <div class="label-total">
        TOTAL DITERIMA
    </div>

    <div class="total">
        Rp {{ number_format($payroll->total_gaji ?? 0,0,',','.') }}
    </div>

</div>

{{-- ===========================
     TERBILANG
=========================== --}}

<div class="terbilang">

    {{ terbilang($payroll->total_gaji ?? 0) }}

</div>

<div class="divider"></div>

{{-- ===========================
     FOOTER
     (PART 3)
=========================== --}}
<div class="footer-grid">

    {{-- ===========================
         KETERANGAN
    ============================ --}}

    <div class="footer-left">

        <strong>Keterangan</strong>

        <br><br>

        Slip gaji ini merupakan bukti pembayaran gaji pegawai
        untuk periode

        <strong>
            {{ \Carbon\Carbon::create()->month($payroll->bulan)->translatedFormat('F') }}
            {{ $payroll->tahun }}
        </strong>.

        <br><br>

        Total pendapatan :

        <strong>
            Rp {{ number_format($payroll->subtotal ?? 0,0,',','.') }}
        </strong>

        @if($bonus->sum('subtotal') > 0)

            <br>

            Bonus :

            <strong>
                Rp {{ number_format($bonus->sum('subtotal'),0,',','.') }}
            </strong>

        @endif

        @if($potongan->sum('subtotal') > 0)

            <br>

            Potongan :

            <strong>
                Rp {{ number_format($potongan->sum('subtotal'),0,',','.') }}
            </strong>

        @endif

        <br><br>

        Status Payroll :

        <strong>
            {{ strtoupper($payroll->status) }}
        </strong>

    </div>

    {{-- ===========================
         TANDA TANGAN
    ============================ --}}

    <div class="footer-right">

        {{ $yayasan?->kota ?? 'Serang' }},
        {{ now()->translatedFormat('d F Y') }}

        <br><br>

        Mengetahui

        <br>

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

    <strong>Slip Gaji Resmi</strong>

    &nbsp;•&nbsp;

    Dokumen ini diterbitkan secara otomatis oleh sistem.

    &nbsp;•&nbsp;

    Dicetak :
    {{ now()->translatedFormat('d F Y H:i') }}

    <br>

    Powered by
    <strong>SantriOne</strong>
    &nbsp;•&nbsp;
    Qinara Indonesia

</div>

</div>

<script>

window.onload = function () {
    window.print();
};

</script>

</body>
</html>