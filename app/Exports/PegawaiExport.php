<?php

namespace App\Exports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use App\Models\Yayasan;

class PegawaiExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomStartCell, WithColumnFormatting
{
    public function startCell(): string
    {
        return 'A5'; // 🔥 tabel mulai dari baris 5
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // kolom NIK
        ];
    }

    public function collection()
    {
        $data = [];
        $no = 1;

        $pegawais = Pegawai::with('lembagas')->get();

        foreach ($pegawais as $pegawai) {
            foreach ($pegawai->lembagas as $lembaga) {
                $data[] = [
                    $no++,
                    $pegawai->nama,
                    $pegawai->niy,
                    $pegawai->nik,
                    $pegawai->jenis_kelamin,
                    $pegawai->no_hp,
                    $pegawai->pendidikan,
                    $pegawai->universitas,
                    $lembaga->nama,
                    $lembaga->pivot->jabatan,
                    $lembaga->pivot->status,
                ];
            }
        }

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'NIY',
            'NIK',
            'JK',
            'No HP',
            'Pendidikan',
            'Universitas',
            'Lembaga',
            'Jabatan',
            'Status',
        ];
    }

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {

            $sheet = $event->sheet->getDelegate();

            // 🔥 JUDUL
            $sheet->mergeCells('A1:K1');
            $sheet->setCellValue('A1', 'DATA GURU & PEGAWAI');

            $namaYayasan = (\Filament\Facades\Filament::getTenant()?->nama ?? auth()->user()?->yayasan?->nama ?? '-');

            $sheet->mergeCells('A2:K2');
            $sheet->setCellValue('A2', strtoupper($namaYayasan));

            // 🔥 STYLE JUDUL
            $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

            // 🔥 HEADER (row 5 sekarang aman)
            $sheet->getStyle('A5:K5')->getFont()->setBold(true);

            // 🔥 BORDER
            $lastRow = $sheet->getHighestRow();
            $sheet->getStyle("A5:K{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle('thin');
        },
    ];
}
}