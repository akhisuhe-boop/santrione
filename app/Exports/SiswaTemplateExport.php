<?php

namespace App\Exports;

use App\Models\Lembaga;
use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SiswaTemplateExport implements FromArray, WithStyles
{
    public function array(): array
    {
        // Header kolom
        $header = [
            'lembaga_id',
            'kelas_id',
            'rfid',
            'nis',
            'nisn',
            'nik',
            'nama_lengkap',
            'jenis_kelamin',
            'tempat_lahir',
            'tanggal_lahir',
            'tinggi_badan',
            'berat_badan',
            'golongan_darah',
            'alamat_jalan',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'desa',
            'rt',
            'rw',
            'kode_pos',
            'no_kartu_keluarga',
            'nik_ayah',
            'nama_ayah',
            'status_ayah',
            'pekerjaan_ayah',
            'pendidikan_ayah',
            'penghasilan_ayah',
            'wa_ayah',
            'nik_ibu',
            'nama_ibu',
            'status_ibu',
            'pekerjaan_ibu',
            'pendidikan_ibu',
            'penghasilan_ibu',
            'wa_ibu',
            'nik_wali',       
            'nama_wali',      
            'hubungan_wali',  
            'status_wali',    
            'pekerjaan_wali', 
            'pendidikan_wali',
            'penghasilan_wali',
            'wa_wali',        
            'status_siswa',
        ];

        // Baris contoh data siswa
        $dummy = [
            1,2,'1234567890','10001','200011001','317011001','Budi Santoso','L','Jakarta','2008-01-01',
            160,50,'O','Jl. Merdeka 1','DKI Jakarta','Jakarta Pusat','Gambir','Senen','001','002',
            '10110','31701100100001','317011001','Santoso','Hidup','Wiraswasta','S1',5000000,'0812345678',
            '317011002','Siti','Hidup','Ibu Rumah Tangga','S1',4000000,'0812345679',
            '317011002','Siti','Bibi','Hidup','Ibu Rumah Tangga','S1',4000000,'0812345679','Aktif',
        ];

        $notes = [
            [],
            ['Catatan: Untuk lembaga_id dan kelas_id gunakan nomor ID dari daftar lembaga & kelas di bawah ini']
        ];

        $lembagaList = Lembaga::select('id', 'nama')->get()->map(function($l) {
            return [$l->id, $l->nama];
        })->toArray();

        $kelasList = Kelas::select('id', 'nama')->get()->map(function($k) {
            return [$k->id, $k->nama];
        })->toArray();

        return array_merge(
            [$header],
            [$dummy],
            $notes,
            [['Daftar Lembaga (ID - Nama)']],
            $lembagaList,
            [['Daftar Kelas (ID - Nama)']],
            $kelasList
        );
    }

    public function styles(Worksheet $sheet)
    {
        // border hanya untuk header + dummy data
        $sheet->getStyle('A1:AT2')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // header bold
        $sheet->getStyle('A1:AT1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // format text supaya NIK/NISN tidak berubah
        $sheet->getStyle('A:AT')->getNumberFormat()->setFormatCode('@');

        return [];
    }
}