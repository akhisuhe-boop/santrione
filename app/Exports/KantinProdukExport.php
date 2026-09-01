<?php

namespace App\Exports;

use App\Models\KantinProduk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class KantinProdukExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    public function startCell(): string
    {
        return 'A5';
    }

    public function collection()
    {
        $data = [];
        $no = 1;

        $produks = KantinProduk::with('kantin')->get();

        foreach ($produks as $p) {
            $data[] = [
                $no++,
                $p->nama,
                $p->barcode,
                $p->kategori,
                $p->harga,
                $p->stok,
                $p->kantin->nama ?? '-',
                $p->is_active ? 'Aktif' : 'Nonaktif',
            ];
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Produk',
            'Barcode',
            'Kategori',
            'Harga',
            'Stok',
            'Kantin',
            'Status',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:H1');
                $sheet->setCellValue('A1', 'DATA PRODUK KANTIN');

                $namaYayasan = (\Filament\Facades\Filament::getTenant()?->nama ?? auth()->user()?->yayasan?->nama ?? '-');

                $sheet->mergeCells('A2:H2');
                $sheet->setCellValue('A2', strtoupper($namaYayasan));

                $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

                $sheet->getStyle('A5:H5')->getFont()->setBold(true);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A5:H{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');
            },
        ];
    }
}
