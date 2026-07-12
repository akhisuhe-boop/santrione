<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi {{ $pembayaran->kode }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 py-10">

<div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-lg overflow-hidden">

    {{-- HEADER --}}
    <div class="border-b px-8 py-6">

        <h1 class="text-2xl font-bold">
            KWITANSI PEMBAYARAN
        </h1>

        <p class="text-gray-500 mt-1">
            No. Kwitansi :
            <span class="font-semibold text-slate-800">
                {{ $pembayaran->kode }}
            </span>
        </p>

    </div>

    {{-- ISI --}}
    <div class="p-8 space-y-5">

        <div class="grid grid-cols-3 gap-4">

            <div class="text-gray-500">
                Nama
            </div>

            <div class="col-span-2 font-semibold">
                {{ $pembayaran->siswa?->nama_lengkap ?? $pembayaran->ppdb?->nama_lengkap }}
            </div>

            <div class="text-gray-500">
                Tagihan
            </div>

            <div class="col-span-2">
                {{ $pembayaran->tagihan->judul }}
            </div>

            <div class="text-gray-500">
                Nominal
            </div>

            <div class="col-span-2 font-bold text-green-600 text-xl">
                Rp {{ number_format($pembayaran->nominal,0,',','.') }}
            </div>

            <div class="text-gray-500">
                Metode
            </div>

            <div class="col-span-2 capitalize">
                {{ $pembayaran->metode }}
            </div>

            <div class="text-gray-500">
                Status
            </div>

            <div class="col-span-2">
                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold">
                    {{ strtoupper($pembayaran->status) }}
                </span>
            </div>

            <div class="text-gray-500">
                Tanggal
            </div>

            <div class="col-span-2">
                {{ optional($pembayaran->tanggal_bayar)->format('d F Y H:i') }}
            </div>

        </div>

    </div>

    {{-- FOOTER --}}
    <div class="border-t px-8 py-6 flex justify-between items-end">

        <div class="text-sm text-gray-500">
            Dokumen ini dibuat otomatis oleh sistem.
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