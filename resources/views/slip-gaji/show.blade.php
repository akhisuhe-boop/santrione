<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji {{ $payroll->kode }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 py-10">

<div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">

    {{-- HEADER --}}
    <div class="border-b px-8 py-6">

        <h1 class="text-2xl font-bold">
            SLIP GAJI
        </h1>

        <p class="text-gray-500 mt-1">
            No. Slip :
            <span class="font-semibold text-slate-800">
                {{ $payroll->kode }}
            </span>
        </p>

    </div>

    {{-- ISI --}}
    <div class="p-8 space-y-6">

        <div class="grid grid-cols-3 gap-4">

            <div class="text-gray-500">
                Nama Pegawai
            </div>

            <div class="col-span-2 font-semibold">
                {{ $payroll->pegawai->nama }}
            </div>

            <div class="text-gray-500">
                Jabatan
            </div>

            <div class="col-span-2">
                {{ $payroll->pegawai->jabatan?->nama ?? '-' }}
            </div>

            <div class="text-gray-500">
                Periode
            </div>

            <div class="col-span-2">
                {{ \Carbon\Carbon::create()->month($payroll->bulan)->translatedFormat('F') }}
                {{ $payroll->tahun }}
            </div>

            <div class="text-gray-500">
                Pendapatan
            </div>

            <div class="col-span-2">
                Rp {{ number_format($payroll->subtotal,0,',','.') }}
            </div>

            <div class="text-gray-500">
                Bonus
            </div>

            <div class="col-span-2 text-blue-600">
                + Rp {{ number_format($payroll->bonus,0,',','.') }}
            </div>

            <div class="text-gray-500">
                Potongan
            </div>

            <div class="col-span-2 text-red-600">
                - Rp {{ number_format($payroll->potongan,0,',','.') }}
            </div>

            <div class="text-gray-500">
                Total Diterima
            </div>

            <div class="col-span-2 font-bold text-2xl text-emerald-600">
                Rp {{ number_format($payroll->total_gaji,0,',','.') }}
            </div>

            <div class="text-gray-500">
                Status
            </div>

            <div class="col-span-2">

                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold">

                    {{ strtoupper($payroll->status) }}

                </span>

            </div>

            <div class="text-gray-500">
                Tanggal Dibayar
            </div>

            <div class="col-span-2">

                {{ optional($payroll->tanggal_bayar)->format('d F Y H:i') }}

            </div>

        </div>

        <hr>

        {{-- RINCIAN PENDAPATAN --}}
        <div>

            <h2 class="font-bold mb-3">
                Rincian Pendapatan
            </h2>

            <table class="w-full text-sm">

                <thead>

                    <tr class="border-b">

                        <th class="text-left py-2">
                            Komponen
                        </th>

                        <th class="text-right">
                            Nominal
                        </th>

                    </tr>

                </thead>

                <tbody>

                @foreach($payroll->items as $item)

                    <tr class="border-b">

                        <td class="py-2">
                            {{ $item->nama_komponen }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($item->subtotal,0,',','.') }}
                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    </div>

    {{-- FOOTER --}}
    <div class="border-t px-8 py-6 flex justify-between items-end">

        <div class="text-sm text-gray-500">

            Slip gaji ini dibuat otomatis oleh sistem.

        </div>

        <div class="text-center">

            <p class="text-sm text-gray-500">

                Bendahara

            </p>

            <div class="h-20"></div>

            <p class="font-semibold">

                ____________________

            </p>

        </div>

    </div>

</div>

</body>
</html>