<?php

namespace App\Imports;

use App\Models\KantinProduk;
use App\Models\Kantin;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class KantinProdukImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            // SKIP HEADER
            if ($index === 0) {
                continue;
            }

            // SKIP BARIS KOSONG
            if (! isset($row[0]) || trim((string) $row[0]) === '') {
                continue;
            }

            $nama = trim((string) $row[0]);
            $barcode = isset($row[1]) ? trim((string) $row[1]) : null;
            $kategori = isset($row[2]) ? trim((string) $row[2]) : null;
            $harga = isset($row[3]) ? (int) preg_replace('/[^0-9]/', '', (string) $row[3]) : 0;
            $stok = (isset($row[4]) && trim((string) $row[4]) !== '') ? (int) $row[4] : null;
            $kantinId = $row[5] ?? null;

            if (! $kantinId) {
                continue;
            }

            $kantin = Kantin::find($kantinId);

            if (! $kantin) {
                continue;
            }

            $existing = KantinProduk::where('kantin_id', $kantin->id)
                ->where('nama', $nama)
                ->first();

            if (blank($barcode)) {
                // Produk sudah ada & barcode di baris import dikosongkan
                // -> JANGAN timpa barcode yang sudah dipakai/dicetak.
                // Cuma generate baru kalau ini produk baru.
                $barcode = $existing?->barcode ?: ('PRD-' . strtoupper(Str::random(8)));
            }

            KantinProduk::updateOrCreate(
                [
                    'kantin_id' => $kantin->id,
                    'nama' => $nama,
                ],
                [
                    'barcode' => $barcode,
                    'kategori' => $kategori,
                    'harga' => $harga,
                    'stok' => $stok,
                    'is_active' => true,
                ]
            );
        }
    }
}
