<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }

        .title { text-align: center; font-weight: bold; font-size: 16px; }
        .subtitle { text-align: center; margin-bottom: 4px; }

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
        .summary-table td { border: none; padding: 3px 6px; }
    </style>
</head>
<body>

<div class="title">LAPORAN KANTIN</div>
<div class="subtitle">{{ $lembagaNama }}</div>
<div class="subtitle">Periode : {{ $periode }}</div>

<br>

<table class="summary-table">
    <tr>
        <td><b>Total Transaksi</b></td>
        <td>: {{ number_format($totalTransaksi, 0, ',', '.') }}</td>
        <td style="width:40px;"></td>
        <td><b>Total Omzet</b></td>
        <td>: Rp {{ number_format($totalOmzet, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td><b>Wallet</b></td>
        <td>: {{ number_format($walletCount, 0, ',', '.') }} transaksi &middot; Rp {{ number_format($walletTotal, 0, ',', '.') }}</td>
        <td></td>
        <td><b>Tunai</b></td>
        <td>: {{ number_format($tunaiCount, 0, ',', '.') }} transaksi &middot; Rp {{ number_format($tunaiTotal, 0, ',', '.') }} ({{ $rasioTunaiPersen }}%)</td>
    </tr>
</table>

<br>

<table>
    <thead>
        <tr>
            <th>Kode</th>
            <th>Tanggal</th>
            <th>Pembeli</th>
            <th>Tipe</th>
            <th>Lembaga</th>
            <th>Metode</th>
            <th>Item</th>
            <th>Total</th>
            <th>Kasir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $row)
        <tr>
            <td>{{ $row['kode'] }}</td>
            <td>{{ $row['tanggal'] }}</td>
            <td>{{ $row['pembeli'] }}</td>
            <td>{{ $row['tipe'] }}</td>
            <td>{{ $row['lembaga'] }}</td>
            <td>{{ $row['metode'] }}</td>
            <td>{{ $row['item'] }}</td>
            <td class="text-right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
            <td>{{ $row['kasir'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
