<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }

        .title { text-align: center; font-weight: bold; font-size: 16px; }
        .subtitle { text-align: center; margin-bottom: 10px; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background: #eee;
        }

        .text-right { text-align: right; }
    </style>
</head>
<body>

<div class="title">LAPORAN KAS KEUANGAN</div>
<div class="subtitle">{{ $namaLembaga }}</div>
<div class="subtitle">Periode : {{ $periode }}</div>

<table>
    <thead>
        <tr>
            <th>Kode</th>
            <th>Tipe</th>
            <th>Lembaga</th>
            <th>Kategori</th>
            <th>Tanggal</th>
            <th>Kas Masuk</th>
            <th>Kas Keluar</th>
            <th>Rekening</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
        <tr>
            <td>{{ $row['kode'] }}</td>
            <td>{{ $row['tipe'] }}</td>
            <td>{{ $row['lembaga'] }}</td>
            <td>{{ $row['kategori'] }}</td>
            <td>{{ $row['tanggal'] }}</td>
            <td class="text-right">Rp {{ number_format($row['masuk'],0,',','.') }}</td>
            <td class="text-right">Rp {{ number_format($row['keluar'],0,',','.') }}</td>
            <td>{{ $row['rekening'] }}</td>
            <td>{{ $row['keterangan'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<br>

<table>
    <tr>
        <td><b>Total Masuk</b></td>
        <td class="text-right">Rp {{ number_format($totalMasuk,0,',','.') }}</td>
    </tr>
    <tr>
        <td><b>Total Keluar</b></td>
        <td class="text-right">Rp {{ number_format($totalKeluar,0,',','.') }}</td>
    </tr>
    <tr>
        <td><b>Saldo</b></td>
        <td class="text-right">Rp {{ number_format($saldo,0,',','.') }}</td>
    </tr>
</table>

</body>
</html>