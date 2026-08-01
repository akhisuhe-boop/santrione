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
<div class="card">

@if($bgBelakangBase64)
<img class="bg" src="{{ $bgBelakangBase64 }}">
@endif

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