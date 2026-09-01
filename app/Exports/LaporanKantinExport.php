<?php

namespace App\Exports;

use App\Models\KantinTransaksi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanKantinExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $filters;
    protected $rowCount = 0;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function collection()
    {
        $query = KantinTransaksi::query()
            ->with(['siswa', 'pegawai', 'lembaga', 'items'])
            ->when($this->filters['dari'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($this->filters['sampai'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '<=', $v))
            ->when($this->filters['lembaga_id'] ?? null, fn ($q, $v) => $q->where('lembaga_id', $v))
            ->when($this->filters['metode'] ?? null, fn ($q, $v) => $q->where('metode', $v))
            ->orderByDesc('tanggal');

        $data = $query->get()->map(function ($trx) {

            $pembeli = $trx->siswa?->nama_lengkap
                ?? $trx->pegawai?->nama
                ?? 'Umum (Pengunjung)';

            $tipe = $trx->siswa ? 'Siswa' : ($trx->pegawai ? 'Guru / Staf' : 'Pengunjung');

            return [
                'kode' => $trx->kode,
                'tanggal' => $trx->tanggal ? Carbon::parse($trx->tanggal)->translatedFormat('d-m-Y H:i') : '-',
                'pembeli' => $pembeli,
                'tipe' => $tipe,
                'lembaga' => $trx->lembaga?->nama ?? '-',
                'metode' => ucfirst($trx->metode),
                'item' => $trx->items->pluck('nama_produk')->implode(', '),
                'total' => 'Rp ' . number_format($trx->total, 0, ',', '.'),
            ];
        });

        $this->rowCount = $data->count();

        return $data;
    }

    public function headings(): array
    {
        return [
            'Kode', 'Tanggal', 'Pembeli', 'Tipe', 'Lembaga', 'Metode', 'Item', 'Total',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('A1', 'Laporan Kantin');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $periode = 'Semua Periode';

                if (! empty($this->filters['dari']) || ! empty($this->filters['sampai'])) {
                    $periode = ($this->filters['dari'] ?? '...') . ' s/d ' . ($this->filters['sampai'] ?? '...');
                }

                $sheet->setCellValue('A2', 'Periode: ' . $periode);
            },
        ];
    }
}
