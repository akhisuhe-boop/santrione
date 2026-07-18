<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Models\Lembaga;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SiswaExport implements 
    FromCollection, 
    WithHeadings,
    WithStyles, 
    WithEvents, 
    WithCustomStartCell
{
    protected $lembaga_id;
    protected $kelas_id;
    protected $lembaga;
    protected $kelas;

    public function __construct($lembaga_id = null, $kelas_id = null)
    {
        $this->lembaga_id = $lembaga_id;
        $this->kelas_id   = $kelas_id;
        
        if ($lembaga_id) {
            $this->lembaga = Lembaga::with('yayasan')->find($lembaga_id);
        } else {
            $this->lembaga = Lembaga::with('yayasan')->first(); // 🔥 LANGSUNG AMBIL LEMBAGA PERTAMA
        }

        if ($kelas_id) {
            $this->kelas = Kelas::find($kelas_id);
        }
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection()
    {
        $query = Siswa::query();

        if ($this->lembaga_id) {
            $query->where('lembaga_id', $this->lembaga_id);
        }

        if ($this->kelas_id) {
            $query->where('kelas_id', $this->kelas_id);
        }

        return $query->select(
            'nis','nisn','nama_lengkap','jenis_kelamin','tempat_lahir','tanggal_lahir',
            'tinggi_badan','berat_badan','golongan_darah','alamat_jalan','provinsi',
            'kabupaten','kecamatan','desa','rt','rw','kode_pos','no_kartu_keluarga',
            'nik_ayah','nama_ayah','pekerjaan_ayah','pendidikan_ayah','penghasilan_ayah',
            'wa_ayah','nik_ibu','nama_ibu','pekerjaan_ibu','pendidikan_ibu',
            'penghasilan_ibu','wa_ibu',
        )->get();
    }

    public function headings(): array
    {
        return [
            'NIS','NISN','Nama Lengkap','Jenis Kelamin','Tempat Lahir','Tanggal Lahir',
            'Tinggi Badan','Berat Badan','Golongan Darah','Alamat Jalan','Provinsi',
            'Kabupaten','Kecamatan','Desa','RT','RW','Kode POS','No KK',
            'NIK Ayah','Nama Ayah','Pekerjaan Ayah','Pendidikan Ayah','Penghasilan Ayah',
            'WA Ayah','NIK Ibu','Nama Ibu','Pekerjaan Ibu','Pendidikan Ibu',
            'Penghasilan Ibu','WA Ibu',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'AD'; // Kolom terakhir sesuai jumlah field

                // ======================
                // LOGO YAYASAN
                // ======================
                if ($this->lembaga?->yayasan?->logo) {
                    $drawing = new Drawing();
                    $drawing->setPath(public_path('storage/'.$this->lembaga->yayasan->logo));
                    $drawing->setHeight(60);
                    $drawing->setCoordinates('A1');
                    $drawing->setWorksheet($sheet);
                }

                // ======================
                // JUDUL
                // ======================
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', 'DATA SANTRI');

                $sheet->mergeCells("A2:{$lastColumn}2");

                $namaYayasan = $this->lembaga?->yayasan?->nama ?? '-';
                $sheet->setCellValue('A2', strtoupper($namaYayasan));

                $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
                $sheet->getStyle('A2')->getFont()->setSize(13)->setBold(true);

                $sheet->getStyle('A1:A2')
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);              

                // ======================
                // INFO LEMBAGA
                // ======================
                $sheet->setCellValue('A4', 'Unit Lembaga');
                $sheet->setCellValue('B4', ': ' . ($this->lembaga?->nama ?? 'Semua Lembaga'));

                $sheet->setCellValue('A5', 'Kelas');
                $sheet->setCellValue('B5', ': ' . ($this->kelas?->nama ?? 'Semua Kelas'));

                $total = $sheet->getHighestRow() - 8;

                $sheet->setCellValue("A6", "Total Siswa");
                $sheet->setCellValue('B6', ': ' . max($total, 0));

                $sheet->getStyle('A4:A6')->getFont()->setBold(true);

                // ======================
                // HEADER TABLE STYLE
                // ======================
                $sheet->getStyle("A8:{$lastColumn}8")->getFont()->setBold(true);

                $sheet->getStyle("A8:{$lastColumn}8")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFEFEFEF'); // abu tipis

                // ======================
                // BORDER TABLE
                // ======================
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle("A8:{$lastColumn}{$highestRow}")
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                // ======================
                // AUTO WIDTH
                // ======================
                $lastColumnIndex = Coordinate::columnIndexFromString($lastColumn);
                
                for ($i = 1; $i <= $lastColumnIndex; $i++) {
                    $column = Coordinate::stringFromColumnIndex($i);
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}