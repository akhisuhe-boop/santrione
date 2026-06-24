<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Lembaga;

class PegawaiTemplateExport implements FromArray, WithEvents
{
    public function array(): array
    {
        $data = [];

        // 🔥 HEADER
        $data[] = [
            'Nama',
            'NIY',
            'NIK',
            'JK',
            'No HP',
            'Email',
            'Alamat',
            'Pendidikan',
            'Universitas',
            'Golongan',
            'Tanggal Masuk',
            'Lembaga_ID', // 🔥 FIX
            'Jabatan',
            'Status',
        ];

        // 🔥 CONTOH DATA
        $data[] = [
            'Yunita',
            '2425-01-123456',
            '1234567890123456',
            'P',
            '087712345678',
            'yunita@gmail.com',
            'Serang',
            'S1',
            'Untirta',
            'III/a',
            '2024-01-01',
            '1',
            'Guru',
            'Tetap',
        ];

        // 🔥 SPASI
        $data[] = [''];

        // 🔥 CATATAN
        $data[] = ['Catatan: Gunakan lembaga_id sesuai daftar di bawah'];

        // 🔥 JUDUL LIST
        $data[] = ['Daftar Lembaga (ID - Nama)'];

        // 🔥 AMBIL DATA LEMBAGA
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

                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();

                // 🔥 HEADER BOLD
                $sheet->getStyle("A1:{$lastCol}1")
                    ->getFont()
                    ->setBold(true);

                // 🔥 BORDER TABLE (HANYA HEADER + DATA)
                $sheet->getStyle("A1:{$lastCol}2")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                // 🔥 CATATAN BOLD
                $sheet->getStyle("A4")->getFont()->setBold(true);

                // 🔥 LIST LEMBAGA BOLD
                $sheet->getStyle("A5")->getFont()->setBold(true);
            },
        ];
    }
}