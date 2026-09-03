<?php

namespace App\Exports;

use App\Models\JadwalPelajaran;
use App\Models\JurnalMengajar;
use App\Models\Pegawai;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailMonitoringMengajarExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected Pegawai $pegawai;
    protected string $tanggalMulai;
    protected string $tanggalSelesai;

    public function __construct(Pegawai $pegawai, string $tanggalMulai, string $tanggalSelesai)
    {
        $this->pegawai = $pegawai;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
    }

    public function collection()
    {
        // Logika PERSIS sama dengan resources/views/filament/pages/detail-monitoring-mengajar.blade.php
        // -- supaya angka di Excel selalu konsisten dengan yang tampil di layar.
        $jadwals = JadwalPelajaran::query()
            ->where('pegawai_id', $this->pegawai->id)
            ->orderByRaw("
                CASE hari
                    WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3
                    WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6
                    WHEN 'minggu' THEN 7
                END
            ")
            ->orderBy('jam_ke')
            ->get();

        $periode = CarbonPeriod::create($this->tanggalMulai, $this->tanggalSelesai);

        $rows = collect();

        foreach ($jadwals as $jadwal) {
            foreach ($periode as $tanggal) {

                $hariTanggal = strtolower($tanggal->locale('id')->dayName);

                if ($hariTanggal !== strtolower($jadwal->hari)) {
                    continue;
                }

                $jurnal = JurnalMengajar::query()
                    ->where('pegawai_id', $this->pegawai->id)
                    ->where('kelas_id', $jadwal->kelas_id)
                    ->where('mata_pelajaran_id', $jadwal->mata_pelajaran_id)
                    ->where('jam_pelajaran_id', $jadwal->jam_pelajaran_id)
                    ->whereDate('tanggal', $tanggal)
                    ->where('status', 'valid')
                    ->first();

                $rows->push([
                    'hari' => ucfirst($jadwal->hari),
                    'tanggal' => $tanggal->translatedFormat('d M Y'),
                    'mapel' => $jadwal->mataPelajaran->nama ?? '-',
                    'kelas' => $jadwal->kelas->nama ?? '-',
                    'jam_ke' => $jadwal->jam_ke,
                    'jp' => $jadwal->durasi_jam,
                    'status' => $jurnal ? 'Mengajar' : 'Tidak Mengajar',
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['Hari', 'Tanggal', 'Mapel', 'Kelas', 'Jam Ke', 'JP', 'Status'];
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
                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', 'Detail Monitoring Mengajar');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->setCellValue('A2', 'Guru: ' . $this->pegawai->nama);
                $sheet->setCellValue('A3', 'Periode: ' . \Carbon\Carbon::parse($this->tanggalMulai)->translatedFormat('d M Y') . ' - ' . \Carbon\Carbon::parse($this->tanggalSelesai)->translatedFormat('d M Y'));
            },
        ];
    }
}
