<?php

namespace App\Exports;

use App\Models\Kas;
use App\Models\Lembaga;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithEvents
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class LaporanKasExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $filters;
    protected $rowCount;
    protected $totalMasuk = 0;
    protected $totalKeluar = 0;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function startCell(): string
    {
        return 'A4'; // 🔥 HEADINGS MULAI DI BARIS 4
    }

    public function collection()
    {
        $query = Kas::query()
            ->with([
                'lembaga',
                'kategori',
                'rekening',
                'pembayaran',
                'pembayaran.siswa',
                'pembayaran.siswa.kelas',
                'pembayaran.tagihan'
            ])

            ->when($this->filters['dari'], fn ($q) =>
                $q->whereDate('tanggal', '>=', $this->filters['dari']))

            ->when($this->filters['sampai'], fn ($q) =>
                $q->whereDate('tanggal', '<=', $this->filters['sampai']))

            ->when($this->filters['tipe'], fn ($q) =>
                $q->where('tipe', $this->filters['tipe']))

            ->when($this->filters['kategori_id'], fn ($q) =>
                $q->where('kategori_id', $this->filters['kategori_id']))

            ->when($this->filters['lembaga_id'], fn ($q) =>
                $q->where('lembaga_id', $this->filters['lembaga_id']))

            ->when($this->filters['rekening_id'] ?? null, fn ($q) =>
                $q->where('rekening_id', $this->filters['rekening_id']))

            ->when(empty($this->filters['tampilkan_alumni']), fn ($q) =>
                $q->where(function ($sub) {
                    $sub->whereDoesntHave('pembayaran.siswa')
                        ->orWhereHas('pembayaran.siswa', fn ($s) =>
                            $s->where('status_siswa', 'Aktif'));
                })
            );

        $data = $query->get()->map(function ($item) {

            /*
            |--------------------------------------------------------------------------
            | 🔥 KATEGORI (SPP April 2026)
            |--------------------------------------------------------------------------
            */
            $kategori = $item->kategori?->nama ?? '-';

            if (strtolower($kategori) === 'spp' && $item->tanggal) {
                $bulan = Carbon::parse($item->tanggal)->translatedFormat('F Y');
                $kategori = "SPP ({$bulan})";
            }

            /*
            |--------------------------------------------------------------------------
            | 🔥 KETERANGAN (Nama Anak - Kelas)
            |--------------------------------------------------------------------------
            */
            $keterangan = '-';

            if ($item->pembayaran && $item->pembayaran->siswa) {
                $nama = $item->pembayaran->siswa->nama_lengkap ?? '-';
                $kelas = $item->pembayaran->siswa->kelas->nama ?? '-';

                $keterangan = "{$nama} - {$kelas}";
            } elseif ($item->pembayaran?->tagihan) {
                $tagihan = $item->pembayaran->tagihan;

                $keterangan = trim(
                    ($tagihan->nama ?? '-') . ' - ' . ($tagihan->kelas_nama ?? '-')
                );
            } else {
                $keterangan = $item->penanggung_jawab ?? '-';
            }

            /*
            |--------------------------------------------------------------------------
            | 🔥 FORMAT RUPIAH LANGSUNG
            |--------------------------------------------------------------------------
            */
            $masuk = $item->tipe === 'masuk'
                ? 'Rp ' . number_format($item->nominal, 0, ',', '.')
                : 'Rp 0';

            $keluar = $item->tipe === 'keluar'
                ? 'Rp ' . number_format($item->nominal, 0, ',', '.')
                : 'Rp 0';

            /*
            |--------------------------------------------------------------------------
            | 🔥 TOTAL
            |--------------------------------------------------------------------------
            */
            if ($item->tipe === 'masuk') {
                $this->totalMasuk += $item->nominal;
            } else {
                $this->totalKeluar += $item->nominal;
            }

            return [
                'kode' => $item->kode,
                'tipe' => $item->tipe === 'masuk' ? 'Masuk' : 'Keluar',

                'lembaga' =>
                    $item->pembayaran?->tagihan?->lembaga_nama
                    ?? $item->lembaga?->nama
                    ?? 'Yayasan/Pesantren',

                'kategori' => $kategori,

                'tanggal' => Carbon::parse($item->tanggal)->format('d-m-Y'),

                'masuk' => $masuk,
                'keluar' => $keluar,

                'rekening' => $item->rekening
                    ? "{$item->rekening->bank} ({$item->rekening->no_rekening})"
                    : '-',

                'keterangan' => $keterangan,
            ];
        });

        $this->rowCount = count($data);

        return $data;
    }

    public function headings(): array
    {
        return [
            'Kode',
            'Tipe',
            'Lembaga',
            'Kategori',
            'Tanggal',
            'Kas Masuk',
            'Kas Keluar',
            'Rekening',
            'Keterangan',
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

                /*
                |--------------------------------------------------------------------------
                | 🔥 HEADER
                |--------------------------------------------------------------------------
                */
                $namaLembaga = 'Semua Lembaga';

                if ($this->filters['lembaga_id']) {
                    $lembaga = Lembaga::find($this->filters['lembaga_id']);
                    $namaLembaga = $lembaga?->nama ?? '-';
                }

                if ($this->filters['dari'] && $this->filters['sampai']) {
                    $dari = Carbon::parse($this->filters['dari'])->format('d-m-Y');
                    $sampai = Carbon::parse($this->filters['sampai'])->format('d-m-Y');
                    $periode = "{$dari} s/d {$sampai}";
                } else {
                    $periode = 'All';
                }

                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'LAPORAN KAS KEUANGAN');

                $sheet->mergeCells('A2:I2');
                $sheet->setCellValue('A2', $namaLembaga);

                $sheet->mergeCells('A3:I3');
                $sheet->setCellValue('A3', "Periode : {$periode}");

                $sheet->getStyle('A1:A3')->getFont()->setBold(true)->setSize(14);

                /*
                |--------------------------------------------------------------------------
                | 🔲 BORDER
                |--------------------------------------------------------------------------
                */
                $lastRow = $this->rowCount + 4;

                $sheet->getStyle("A4:I{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                /*
                |--------------------------------------------------------------------------
                | 🔥 TOTAL
                |--------------------------------------------------------------------------
                */
                $totalRow = $lastRow + 1;

                $sheet->setCellValue("E{$totalRow}", 'TOTAL');
                $sheet->setCellValue("F{$totalRow}", 'Rp ' . number_format($this->totalMasuk, 0, ',', '.'));
                $sheet->setCellValue("G{$totalRow}", 'Rp ' . number_format($this->totalKeluar, 0, ',', '.'));

                $sheet->setCellValue("E" . ($totalRow + 1), 'SALDO');
                $sheet->setCellValue("F" . ($totalRow + 1), 'Rp ' . number_format($this->totalMasuk - $this->totalKeluar, 0, ',', '.'));

                $sheet->getStyle("E{$totalRow}:G" . ($totalRow + 1))
                    ->getFont()
                    ->setBold(true);

                /*
                |--------------------------------------------------------------------------
                | ALIGNMENT
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("A1:I3")
                    ->getAlignment()
                    ->setHorizontal('center');
            },
        ];
    }
}