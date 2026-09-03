<?php

namespace App\Exports;

use App\Models\Pegawai;
use App\Filament\Resources\MonitoringMengajarResource;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapitulasiMengajarExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected string $mulai;
    protected string $selesai;

    public function __construct(string $mulai, string $selesai)
    {
        $this->mulai = $mulai;
        $this->selesai = $selesai;
    }

    public function collection()
    {
        $periode = [$this->mulai, $this->selesai];

        // Pakai persis helper yang sama dengan tabel di layar (resolvePeriode/
        // kewajibanJp/realisasiJp di MonitoringMengajarResource) supaya
        // angkanya SELALU konsisten dengan yang tampil di admin panel --
        // termasuk kewajiban yang sudah diskalakan sesuai panjang periode.
        return Pegawai::query()
            ->orderBy('nama')
            ->get()
            ->map(function ($pegawai) use ($periode) {

                $kewajiban = MonitoringMengajarResource::kewajibanJp($pegawai->id, $periode);
                $realisasi = MonitoringMengajarResource::realisasiJp($pegawai->id, $periode);

                $tidakMengajar = max($kewajiban - $realisasi, 0);
                $persentase = $kewajiban > 0 ? round(($realisasi / $kewajiban) * 100) : 0;

                return [
                    'guru' => $pegawai->nama,
                    'kewajiban' => MonitoringMengajarResource::formatJp($kewajiban),
                    'mengajar' => MonitoringMengajarResource::formatJp($realisasi),
                    'tidak_mengajar' => MonitoringMengajarResource::formatJp($tidakMengajar),
                    'persentase' => $persentase . '%',
                ];
            });
    }

    public function headings(): array
    {
        return ['Guru', 'Kewajiban JP', 'Mengajar', 'Tidak Mengajar', 'Persentase'];
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
                $sheet->setCellValue('A2', 'Periode: ' . \Carbon\Carbon::parse($this->mulai)->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse($this->selesai)->translatedFormat('d M Y'));
            },
        ];
    }
}
