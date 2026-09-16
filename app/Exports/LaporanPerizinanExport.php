<?php

namespace App\Exports;

use App\Models\Siswa;
use App\Filament\Resources\LaporanPerizinanResource;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * DITAMBAHKAN (16 Sep 2026) -- export Laporan Perizinan, 1 baris per
 * siswa (Nama, Lembaga, Kelas, Total Hari Izin Disetujui) sesuai
 * periode yang sedang difilter di layar. Pakai maatwebsite/excel
 * langsung (bukan pxlrbt/filament-excel) -- lihat catatan di
 * DetailAbsensiMapelExport.php soal kenapa.
 */
class LaporanPerizinanExport implements FromCollection, WithHeadings, WithMapping
{
    protected array $periode;

    public function __construct(array $periode)
    {
        $this->periode = $periode;
    }

    public function collection(): Collection
    {
        return Siswa::with(['lembaga', 'kelas'])->orderBy('nama_lengkap')->get();
    }

    public function headings(): array
    {
        return ['Nama', 'Lembaga', 'Kelas', 'Total Hari Izin Disetujui'];
    }

    public function map($siswa): array
    {
        return [
            $siswa->nama_lengkap,
            $siswa->lembaga?->nama ?? '-',
            $siswa->kelas?->nama ?? '-',
            LaporanPerizinanResource::totalHariDisetujui($siswa->id, $this->periode),
        ];
    }
}
