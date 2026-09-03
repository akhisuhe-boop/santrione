<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Cetak Barcode Produk</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 24px;
            background: #f3f4f6;
        }

        .toolbar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .toolbar h1 {
            font-size: 18px;
            margin: 0;
        }

        .toolbar button {
            background: #00A39D;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }

        .toolbar button:hover {
            opacity: .9;
        }

        .label-sheet {
            max-width: 900px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }

        .label {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            background: #fff;
            padding: 10px 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .label .nama {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 2px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .label .harga {
            font-size: 11px;
            color: #00A39D;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .label svg {
            max-width: 100%;
        }

        .kosong {
            max-width: 900px;
            margin: 40px auto;
            text-align: center;
            color: #6b7280;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .label {
                border: 1px solid #000;
                break-inside: avoid;
            }

            .label-sheet {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="toolbar">
        <h1>Cetak Barcode Produk ({{ $produks->count() }} label)</h1>
        <button onclick="window.print()">🖨️ Print</button>
    </div>

    @if ($produks->isEmpty())

        <div class="kosong">Tidak ada produk yang dipilih, atau produknya tidak punya barcode.</div>

    @else

        <div class="label-sheet">

            @foreach ($produks as $produk)

                @if ($produk->barcode)

                    <div class="label">
                        <div class="nama">{{ $produk->nama }}</div>
                        <div class="harga">Rp {{ number_format($produk->harga, 0, ',', '.') }}</div>
                        <svg class="barcode" data-barcode="{{ $produk->barcode }}"></svg>
                    </div>

                @endif

            @endforeach

        </div>

    @endif

    <script>
        document.querySelectorAll('.barcode').forEach(function (el) {
            JsBarcode(el, el.dataset.barcode, {
                format: 'CODE128',
                width: 1.6,
                height: 40,
                fontSize: 12,
                margin: 4,
            });
        });
    </script>

</body>
</html>
