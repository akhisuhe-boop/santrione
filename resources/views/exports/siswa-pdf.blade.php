<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data Siswa</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { text-align: center; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 5px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>

<h2>DATA SISWA</h2>

@if($lembaga)
<p><strong>Lembaga:</strong> {{ $lembaga->nama }}</p>
@endif

@if($kelas)
<p><strong>Kelas:</strong> {{ $kelas->nama }}</p>
@endif

<p><strong>Total:</strong> {{ $siswas->count() }} Siswa</p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Jenis Kelamin</th>
            <th>Kelas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($siswas as $i => $siswa)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $siswa->nis }}</td>
            <td>{{ $siswa->nama_lengkap }}</td>
            <td>{{ $siswa->jenis_kelamin }}</td>
            <td>{{ optional($siswa->kelas)->nama }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>