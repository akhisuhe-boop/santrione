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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            width: 80mm;
            margin: auto;
            color: #000;
            font-size: 12px;
            line-height: 1.5;
            padding: 8px;
        }

        .center {
            text-align: center;
        }

        .logo {
            width: 65px;
            height: 65px;
            margin: 0 auto 8px;
            object-fit: contain;
        }

        h1 {
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 2px;
        }

        h2 {
            font-size: 13px;
            font-weight: 700;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 2px 0;
        }

        td:first-child {
            width: 38%;
        }

        td:last-child {
            text-align: right;
        }

        .section-title {
            margin: 6px 0;
            font-size: 12px;
            font-weight: 700;
        }

        .total {
            font-size: 20px;
            font-weight: 800;
        }

        .terbilang {
            border: 1px dashed #000;
            padding: 8px;
            margin-top: 8px;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 11px;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                width: 80mm;
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="center">

        @if($lembaga?->logo)
            <img src="{{ Storage::disk('r2-public')->url($lembaga->logo) }}" class="logo">
        @elseif($yayasan?->logo)
            <img src="{{ Storage::disk('r2-public')->url($yayasan->logo) }}" class="logo">
        @endif

        <h1>{{ strtoupper($yayasan?->nama) }}</h1>
        <h2>{{ strtoupper($lembaga?->nama) }}</h2>

        @if($yayasan?->alamat)
            <div style="font-size:11px">
                {{ $yayasan->alamat }}
            </div>
        @endif

        @if($yayasan?->telepon)
            <div style="font-size:11px">
                Telp. {{ $yayasan->telepon }}
            </div>
        @endif

    </div>

    <div class="divider"></div>

    <div class="center">
        <strong>SLIP GAJI PEGAWAI</strong>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Periode</td>
            <td>
                {{ \Carbon\Carbon::create()->month($payroll->bulan)->translatedFormat('F') }}
                {{ $payroll->tahun }}
            </td>
        </tr>
        <tr>
            <td>Tanggal Bayar</td>
            <td>{{ optional($payroll->tanggal_bayar)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>{{ strtoupper($payroll->status) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td>Nama</td>
            <td>{{ $pegawai->nama }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $lembaga?->pivot?->jabatan ?? '-' }}</td>
        </tr>

        @if($pegawai->nip)
            <tr>
                <td>NIP</td>
                <td>{{ $pegawai->nip }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    {{-- PART 2 : Pendapatan --}}
    {{-- ========================= --}}
{{-- PENDAPATAN --}}
{{-- ========================= --}}

<div class="section-title">
    PENDAPATAN
</div>

<table>

    @forelse($payroll->items as $item)

        <tr>
            <td>{{ $item->nama_komponen }}</td>
            <td>
                Rp {{ number_format($item->subtotal,0,',','.') }}
            </td>
        </tr>

        @if(($item->qty ?? 1) > 1)

            <tr>
                <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                    {{ $item->qty }} × Rp {{ number_format($item->tarif,0,',','.') }}
                </td>
            </tr>

        @endif

    @empty

        <tr>
            <td colspan="2" style="text-align:center;color:#666;">
                Tidak ada komponen pendapatan.
            </td>
        </tr>

    @endforelse

    <tr>
        <td style="padding-top:6px;">
            <strong>Subtotal</strong>
        </td>

        <td style="padding-top:6px;">
            <strong>
                Rp {{ number_format($payroll->subtotal ?? 0,0,',','.') }}
            </strong>
        </td>
    </tr>

</table>

<div class="divider"></div>

{{-- ========================= --}}
{{-- BONUS --}}
{{-- ========================= --}}

@if($bonus->count())

    <div class="section-title">
        BONUS
    </div>

    <table>

        @foreach($bonus as $item)

            <tr>
                <td>{{ $item->nama_komponen }}</td>
                <td>
                    + Rp {{ number_format($item->subtotal,0,',','.') }}
                </td>
            </tr>

            @if(($item->qty ?? 1) > 1)

                <tr>
                    <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                        {{ $item->qty }} × Rp {{ number_format($item->nominal ?? $item->subtotal,0,',','.') }}
                    </td>
                </tr>

            @endif

            @if(!empty($item->catatan))

                <tr>
                    <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                        {{ $item->catatan }}
                    </td>
                </tr>

            @endif

        @endforeach

        <tr>
            <td style="padding-top:6px;">
                <strong>Subtotal</strong>
            </td>

            <td style="padding-top:6px;">
                <strong>
                    + Rp {{ number_format($bonus->sum('subtotal'),0,',','.') }}
                </strong>
            </td>
        </tr>

    </table>

    <div class="divider"></div>

@endif

{{-- ========================= --}}
{{-- POTONGAN --}}
{{-- ========================= --}}

@if($potongan->count())

    <div class="section-title">
        POTONGAN
    </div>

    <table>

        @foreach($potongan as $item)

            <tr>
                <td>{{ $item->nama_komponen }}</td>
                <td>
                    - Rp {{ number_format($item->subtotal,0,',','.') }}
                </td>
            </tr>

            @if(($item->qty ?? 1) > 1)

                <tr>
                    <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                        {{ $item->qty }} × Rp {{ number_format($item->nominal ?? $item->subtotal,0,',','.') }}
                    </td>
                </tr>

            @endif

            @if(!empty($item->catatan))

                <tr>
                    <td colspan="2" style="font-size:10px;color:#666;padding-top:0;">
                        {{ $item->catatan }}
                    </td>
                </tr>

            @endif

        @endforeach

        <tr>
            <td style="padding-top:6px;">
                <strong>Subtotal</strong>
            </td>

            <td style="padding-top:6px;">
                <strong>
                    - Rp {{ number_format($potongan->sum('subtotal'),0,',','.') }}
                </strong>
            </td>
        </tr>

    </table>

    <div class="divider"></div>

@endif

{{-- ========================= --}}
{{-- RINGKASAN PAYROLL --}}
{{-- ========================= --}}

<div class="section-title">
    RINGKASAN PAYROLL
</div>

<table>

    <tr>
        <td>Pendapatan</td>
        <td>
            Rp {{ number_format($payroll->subtotal ?? 0,0,',','.') }}
        </td>
    </tr>

    <tr>
        <td>Bonus</td>
        <td>
            + Rp {{ number_format($bonus->sum('subtotal'),0,',','.') }}
        </td>
    </tr>

    <tr>
        <td>Potongan</td>
        <td>
            - Rp {{ number_format($potongan->sum('subtotal'),0,',','.') }}
        </td>
    </tr>

</table>

<div class="divider"></div>

{{-- ========================= --}}
{{-- TOTAL DITERIMA --}}
{{-- ========================= --}}

<div class="center">

    <div style="font-size:12px;font-weight:600;">
        TOTAL DITERIMA
    </div>

    <div class="total">
        Rp {{ number_format($payroll->total_gaji ?? 0,0,',','.') }}
    </div>

</div>

<div class="terbilang">

    {{ terbilang($payroll->total_gaji ?? 0) }}

</div>

<div class="divider"></div>

{{-- ========================= --}}
{{-- FOOTER --}}
{{-- ========================= --}}

<div class="footer">

    <strong>Slip Gaji Resmi</strong>

    <br>

    Gaji telah dibayarkan kepada pegawai
    sesuai periode payroll.

    <br><br>

    <span style="font-size:10px;color:#555;">
        Dokumen ini merupakan bukti pembayaran gaji yang sah
        <br>
        dan diterbitkan secara otomatis oleh sistem.
    </span>

    <br><br>

    <span style="font-size:10px;color:#777;">
        Dicetak pada
        {{ now()->translatedFormat('d F Y H:i') }}
    </span>

    <br>

    <span style="font-size:10px;color:#777;">
        Powered by
        <strong>SantriOne</strong>
        &nbsp;•&nbsp;
        Qinara Indonesia
    </span>

</div>

<script>

window.onload = function () {
    window.print();
};

</script>

</body>
</html>