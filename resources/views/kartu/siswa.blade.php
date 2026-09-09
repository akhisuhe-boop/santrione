<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cetak Kartu Siswa</title>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<style>
@page {
    size: 30cm 20cm;
    margin: 0.3cm;
}

body{
    font-family: sans-serif;
    margin:0;
}

/* DITAMBAHKAN -- toolbar & tombol print, sama pola dengan
   kantin/cetak-barcode.blade.php. Disembunyikan saat print. */
.toolbar{
    padding:16px 24px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    background:#f3f4f6;
}

.toolbar h1{
    font-size:16px;
    margin:0;
}

.toolbar button{
    background:#00A39D;
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:10px;
    font-weight:600;
    font-size:14px;
    cursor:pointer;
}

@media print{
    .toolbar{ display:none; }
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

/* ====== KARTU BELAKANG -- DITAMBAHKAN ======
   Kotak luar (.card-belakang) ukurannya SAMA PERSIS dengan .card
   (5.4cm x 8.56cm) -- posisi & ukuran kertas di lembar cetak TIDAK
   berubah. Yang dirotasi cuma isinya (.back-rotator), dibuat dengan
   dimensi kebalikannya (8.56cm x 5.4cm, "landscape") lalu diputar
   90 derajat di tengah kotak luar -- supaya background & konten
   belakang bisa didesain landscape tanpa background jadi gepeng/
   terdistorsi seperti sebelumnya (dulu background landscape dipaksa
   masuk kotak portrait apa adanya). */
.card-belakang{
    width:5.4cm;
    height:8.56cm;
    position:relative;
    margin:auto;
    overflow:hidden;
}

.back-rotator{
    position:absolute;
    width:8.56cm;
    height:5.4cm;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%) rotate(90deg);
    transform-origin:center center;
}

.back-bg{
    position:absolute;
    width:100%;
    height:100%;
    object-fit:cover;
}

.back-foto{
    position:absolute;
    top:0.35cm;
    left:0.35cm;
    width:1.7cm;
    height:2cm;
    object-fit:cover;
    border-radius:4px;
}

.back-data{
    position:absolute;
    top:0.35cm;
    left:2.25cm;
    width:6cm;
    font-size:7.5px;
    line-height:1.35;
}

.back-data b{
    display:inline-block;
    width:1.55cm;
}

.back-barcode{
    position:absolute;
    bottom:0.3cm;
    left:50%;
    transform:translateX(-50%);
    text-align:center;
}

.back-barcode svg{
    height:0.9cm;
    max-width:100%;
}
</style>
</head>

<body>

<div class="toolbar">
    <h1>Cetak Kartu Siswa ({{ $siswas->count() }} kartu)</h1>
    <button onclick="window.print()">🖨️ Print</button>
</div>

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
<div class="card-belakang">
<div class="back-rotator">

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
@endphp

@if($fotoBase64Belakang)
<img class="back-foto" src="{{ $fotoBase64Belakang }}">
@endif

<div class="back-data">
<b>Nama</b>: {{ strtoupper($siswa->nama_lengkap) }}<br>
<b>NIS</b>: {{ $siswa->nis }}<br>
<b>NISN</b>: {{ $siswa->nisn }}<br>
<b>Lembaga</b>: {{ strtoupper($siswa->lembaga->nama ?? '-') }}<br>
<b>TTL</b>: {{ $ttl }}<br>
<b>Gol. Darah</b>: {{ $siswa->golongan_darah ?? '-' }}<br>
<b>Alamat</b>: {{ strtoupper($siswa->desa ?? $siswa->kecamatan ?? '-') }}
</div>

<div class="back-barcode">
<svg class="barcode-batang" data-barcode="{{ $siswa->nis }}"></svg>
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

<script>
    document.querySelectorAll('.barcode-batang').forEach(function (el) {
        JsBarcode(el, el.dataset.barcode, {
            format: 'CODE128',
            width: 1.2,
            height: 34,
            fontSize: 9,
            margin: 2,
        });
    });
</script>

</body>
</html>