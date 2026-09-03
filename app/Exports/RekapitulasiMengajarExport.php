<?php

namespace App\Exports;

use App\Models\Pegawai;
use App\Models\Kurikulum;
use App\Models\JurnalMengajar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapitulasiMengajarExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        $mulai = now()->startOfWeek()->toDateString();
        $selesai = now()->endOfWeek()->toDateString();

        return Pegawai::query()
            ->orderBy('nama')
            ->get()
            ->map(function ($pegawai) use ($mulai, $selesai) {

                $kewajiban = Kurikulum::query()
                    ->where('pegawai_id', $pegawai->id)
                    ->sum('jumlah_jam_per_minggu');

                $realisasi = JurnalMengajar::query()
                    ->where('jurnal_mengajars.pegawai_id', $pegawai->id)
                    ->where('status', 'valid')
                    ->whereBetween('jurnal_mengajars.tanggal', [$mulai, $selesai])
                    ->join('jadwal_pelajarans', 'jurnal_mengajars.jadwal_pelajaran_id', '=', 'jadwal_pelajarans.id')
                    ->sum('jadwal_pelajarans.durasi_jam');

                $tidakMengajar = max($kewajiban - $realisasi, 0);
                $persentase = $kewajiban > 0 ? round(($realisasi / $kewajiban) * 100) : 0;

                return [
                    'guru' => $pegawai->nama,
                    'kewajiban' => $kewajiban . ' JP',
                    'mengajar' => $realisasi . ' JP',
                    'tidak_mengajar' => $tidakMengajar . ' JP',
                    'persentase' => $persentase . '%',
                ];
            });
    }

    public function headings(): array
    {
        return ['Guru', 'Kewajiban JP', 'Mengajar (Minggu Ini)', 'Tidak Mengajar (Minggu Ini)', 'Persentase'];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 2);
                $sheet->setCellValue('A1', 'Rekapitulasi Mengajar');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->setCellValue('A2', 'Periode: ' . now()->startOfWeek()->translatedFormat('d M Y') . ' - ' . now()->endOfWeek()->translatedFormat('d M Y'));
            },
        ];
    }
}
