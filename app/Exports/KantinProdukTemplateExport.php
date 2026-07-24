<?php

namespace App\Exports;

use App\Models\Lembaga;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class KantinProdukTemplateExport implements FromArray, WithEvents
{
    public function array(): array
    {
        $data = [];

        $data[] = [
            'Nama',
            'Barcode',
            'Kategori',
            'Harga',
            'Stok',
            'Lembaga_ID',
        ];

        $data[] = [
            'Pulpen',
            'PRD-00001',
            'Peralatan Sekolah',
            '5000',
            '50',
            '1',
        ];

        $data[] = [''];

        $data[] = ['Catatan: Barcode boleh dikosongkan (dibuat otomatis). Kolom Stok boleh dikosongkan kalau tidak mau dilacak. Gunakan Lembaga_ID sesuai daftar di bawah'];

        $data[] = ['Daftar Lembaga (ID - Nama)'];

        $lembagas = Lembaga::orderBy('id')->get();

        foreach ($lembagas as $l) {
            $data[] = [$l->id . ' - ' . $l->nama];
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastCol = $sheet->getHighestColumn();

                $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);

                $sheet->getStyle("A1:{$lastCol}2")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                $sheet->getStyle('A4')->getFont()->setBold(true);
                $sheet->getStyle('A5')->getFont()->setBold(true);
            },
        ];
    }
}
