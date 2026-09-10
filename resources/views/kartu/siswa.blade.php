<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
@page {
    size: 30cm 20cm;
    margin: 0.3cm;
}

body{
    font-family: sans-serif;
    margin:0;
}

table{
    width:100%;
    border-collapse:separate;
    border-spacing:0.35cm 0.7cm;
}

td{
    width:20%;
    text-align:center;
    vertical-align:top;
}

.card{
    width:5.4cm;
    height:8.56cm;
    position:relative;
    margin:auto;
}

.bg{
    position:absolute;
    width:100%;
    height:100%;
    object-fit:cover;
}

.foto{
    position:absolute;
    top:2.1cm;
    left:50%;
    transform:translateX(-50%);
    width:2cm;
    height:2.2cm;
    object-fit:cover;
    border-radius:5px;
}

.nama{
    position:absolute;
    top:4.45cm;
    width:100%;
    text-align:center;
    font-size:13px;
    font-weight:bold;
    letter-spacing:0.5px;
}

.nisn{
    position:absolute;
    top:4.85cm;
    width:100%;
    text-align:center;
    font-size:10px;
}

.lembaga{
    position:absolute;
    top:5.17cm;
    width:100%;
    text-align:center;
    font-size:10px;
}

.qr{
    position:absolute;
    bottom:1.25cm;
    left:50%;
    transform:translateX(-50%);
}

.nis{
    position:absolute;
    bottom:0.95cm;
    width:100%;
    text-align:center;
    font-size:8px;
    font-weight:bold;
}

/* ====== KARTU BELAKANG -- LANDSCAPE, DIROTASI KE PORTRAIT ======
   Isi konten (judul, foto, data, barcode) TERBUKTI RAPI tanpa nabrak
   template saat dites landscape murni tanpa rotasi -- jadi masalah
   sebelumnya BUKAN di layout kontennya, tapi di cara rotasinya.

   PERBEDAAN dari percobaan rotasi sebelumnya: dulu "menengahkan" dan
   "memutar" digabung jadi SATU transform (translate(-50%,-50%)
   rotate(90deg)) -- diduga DomPDF salah hitung kalau 2 fungsi
   transform digabung begitu. Sekarang dipisah: menengahkan pakai
   margin negatif biasa (bukan transform), dan transform CUMA berisi
   SATU fungsi (rotate(90deg) saja). */
.card-belakang-wrap{
    width:5.4cm;
    height:8.56cm;
    position:relative;
    margin:auto;
    overflow:hidden;
}

.card-belakang{
    width:8.56cm;
    height:5.4cm;
    position:absolute;
    top:50%;
    left:50%;
    margin-top:-2.7cm;
    margin-left:-4.28cm;
    transform:rotate(90deg);
    padding:0.35cm 0.5cm;
    box-sizing:border-box;
}

.back-bg{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    object-fit:cover;
    z-index:0;
}

.back-inner{
    position:relative;
    z-index:1;
}

.back-title{
    font-size:13px;
    font-weight:bold;
    margin-bottom:0.2cm;
}

table.back-layout{
    width:60%;
    border-collapse:collapse;
}

.back-foto-cell{
    width:2.1cm;
    vertical-align:top;
}

.back-foto{
    width:1.9cm;
    height:2.3cm;
    object-fit:cover;
    border-radius:4px;
}

.back-data-cell{
    vertical-align:top;
    padding-left:0.3cm;
}

.back-data table{
    width:100%;
    border-collapse:collapse;
}

.back-data td{
    text-align:left;
    vertical-align:top;
    font-size:8px;
    line-height:1.55;
    padding:0;
}

.back-data td.label{
    width:1.5cm;
    font-weight:bold;
    white-space:nowrap;
}

.back-data td.titik{
    width:0.2cm;
}

.back-barcode{
    margin-top:0.25cm;
}

.back-barcode img{
    height:0.85cm;
}
</style>
</head>

<body>

@php
    $bgDepanBase64 = null;
    $bgBelakangBase64 = null;
    try {
        if ($template?->background_depan) {
            $bgDepanBase64 = 'data:image/png;base64,' . base64_encode(\Storage::disk('r2-public')->get($template->background_depan));
        }
        if ($template?->background_belakang) {
            $bgBelakangBase64 = 'data:image/png;base64,' . base64_encode(\Storage::disk('r2-public')->get($template->background_belakang));
        }
    } catch (\Throwable $e) {
        // biarkan null kalau gagal ambil dari R2, kartu tetap tercetak tanpa background
    }
@endphp

{{-- KARTU DEPAN --}}
@foreach($siswas->chunk(10) as $chunk)

<table>
<tr>
@foreach($chunk as $i => $siswa)

<td>
<div class="card">

@if($bgDepanBase64)
<img class="bg" src="{{ $bgDepanBase64 }}">
@endif

@php
    $fotoBase64 = null;
    if ($siswa->foto) {
        try {
            $fotoBase64 = 'data:image/png;base64,' . base64_encode(\Storage::disk('r2-public')->get($siswa->foto));
        } catch (\Throwable $e) {
            $fotoBase64 = null;
        }
    }
@endphp
@if($fotoBase64)
<img class="foto" src="{{ $fotoBase64 }}">
@endif

<div class="nama">
{{ strtoupper($siswa->nama_lengkap) }}
</div>

<div class="nisn">
NISN : {{ $siswa->nisn }}
</div>

<div class="lembaga">
{{ strtoupper($siswa->lembaga->nama ?? '-') }}
</div>

<div class="qr">
<img width="63"
src="data:image/png;base64,{!! 
    base64_encode(
        QrCode::size(100)->generate($siswa->nis)
    )
!!}">
</div>

<div class="nis">
NIS : {{ $siswa->nis }}
</div>

</div>
</td>

@if(($i+1) % 5 == 0 && !$loop->last)
</tr><tr>
@endif

@endforeach
</tr>
</table>

@endforeach


{{-- KARTU BELAKANG --}}
@foreach($siswas->chunk(10) as $chunk)

<table>
<tr>
@foreach($chunk as $i => $siswa)

<td>
<div class="card-belakang-wrap">
<div class="card-belakang">

@if($bgBelakangBase64)
<img class="back-bg" src="{{ $bgBelakangBase64 }}">
@endif

@php
    $fotoBase64Belakang = null;
    if ($siswa->foto) {
        try {
            $fotoBase64Belakang = 'data:image/png;base64,' . base64_encode(\Storage::disk('r2-public')->get($siswa->foto));
        } catch (\Throwable $e) {
            $fotoBase64Belakang = null;
        }
    }

    $ttl = trim(($siswa->tempat_lahir ?? '-') . ', ' . ($siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d M Y') : '-'));

    $barcodeBase64 = null;
    try {
        $barcodeBase64 = \Milon\Barcode\Facades\DNS1DFacade::getBarcodePNG($siswa->nis, 'C128', 2, 40);
    } catch (\Throwable $e) {
        $barcodeBase64 = null;
    }
@endphp

<div class="back-inner">

<div class="back-title">KARTU TANDA PELAJAR</div>

<table class="back-layout">
<tr>
<td class="back-foto-cell">
@if($fotoBase64Belakang)
<img class="back-foto" src="{{ $fotoBase64Belakang }}">
@endif
</td>
<td class="back-data-cell">
<div class="back-data">
<table>
<tr><td class="label">Nama</td><td class="titik">:</td><td>{{ strtoupper($siswa->nama_lengkap) }}</td></tr>
<tr><td class="label">NIS/NISN</td><td class="titik">:</td><td>{{ $siswa->nis }}/{{ $siswa->nisn }}</td></tr>
<tr><td class="label">TTL</td><td class="titik">:</td><td>{{ $ttl }}</td></tr>
<tr><td class="label">Lembaga</td><td class="titik">:</td><td>{{ strtoupper($siswa->lembaga->nama ?? '-') }}</td></tr>
<tr><td class="label">Alamat</td><td class="titik">:</td><td>{{ strtoupper($siswa->desa ?? $siswa->kecamatan ?? '-') }}</td></tr>
</table>
</div>
</td>
</tr>
</table>

@if($barcodeBase64)
<div class="back-barcode">
<img src="data:image/png;base64,{{ $barcodeBase64 }}">
</div>
@endif

</div>

</div>
</div>
</td>

@if(($i+1) % 5 == 0 && !$loop->last)
</tr><tr>
@endif

@endforeach
</tr>
</table>

@if(!$loop->last)
<div style="page-break-after:always;"></div>
@endif

@endforeach

</body>
</html>