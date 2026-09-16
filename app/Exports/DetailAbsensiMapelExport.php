<?php

namespace App\Exports;

use App\Models\AbsensiMapel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * DITAMBAHKAN (16 Sep 2026) -- export detail per kejadian buat
 * Laporan Absensi Mapel Siswa (1 baris = 1 kali absen mapel, BUKAN
 * rekap angka per siswa). Dipakai langsung lewat maatwebsite/excel
 * (bukan lewat wrapper pxlrbt/filament-excel) karena
 * ExcelExport::make()->fromCollection() TERNYATA tidak ada di
 * package itu -- ketauan lewat error 500 di production/dev pas
 * dites, bukan cuma dugaan di kepala. maatwebsite/excel API-nya
 * jelas & terdokumentasi, jadi dipakai langsung supaya tidak
 * menebak-nebak lagi.
 */
class DetailAbsensiMapelExport implements FromCollection, WithHeadings, WithMapping
{
    /** @var Collection<int, AbsensiMapel> */
    protected Collection $riwayat;

    public function __construct(Collection $riwayat)
    {
        $this->riwayat = $riwayat;
    }

    public function collection(): Collection
    {
        return $this->riwayat;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Jam Ke', 'Nama Siswa', 'Kelas', 'Mapel', 'Guru', 'Status', 'Keterangan'];
    }

    public function map($item): array
    {
        return [
            \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
            $item->jurnalMengajar?->jamPelajaran?->nama ?? '-',
            $item->siswa?->nama_lengkap ?? '-',
            $item->siswa?->kelas?->nama ?? '-',
            $item->jadwalPelajaran?->mataPelajaran?->nama ?? '-',
            $item->jurnalMengajar?->pegawai?->nama ?? '-',
            $item->status,
            $item->keterangan ?? '-',
        ];
    }
}
