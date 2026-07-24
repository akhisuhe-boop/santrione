<?php

namespace App\Services;

use App\Models\Kas;
use App\Models\Lembaga;
use Carbon\Carbon;

class LaporanKasPdf
{
    public static function getData($filters)
    {
        $query = Kas::query()
            ->with([
                'lembaga',
                'kategori',
                'rekening',
                'pembayaran',
                'pembayaran.siswa.kelas',
                'pembayaran.tagihan'
            ])

            ->when($filters['dari'], fn ($q) =>
                $q->whereDate('tanggal', '>=', $filters['dari']))

            ->when($filters['sampai'], fn ($q) =>
                $q->whereDate('tanggal', '<=', $filters['sampai']))

            ->when($filters['tipe'], fn ($q) =>
                $q->where('tipe', $filters['tipe']))

            ->when($filters['kategori_id'], fn ($q) =>
                $q->where('kategori_id', $filters['kategori_id']))

            ->when($filters['lembaga_id'], fn ($q) =>
                $q->where('lembaga_id', $filters['lembaga_id']))

            ->when($filters['rekening_id'] ?? null, fn ($q) =>
                $q->where('rekening_id', $filters['rekening_id']))

            ->when($filters['diinput_oleh'] ?? null, fn ($q) =>
                $q->where('diinput_oleh', $filters['diinput_oleh']))

            ->when($filters['kelas_id'] ?? null, fn ($q) =>
                $q->whereHas('pembayaran.siswa', fn ($s) =>
                    $s->where('kelas_id', $filters['kelas_id'])))

            ->when(empty($filters['tampilkan_alumni']), fn ($q) =>
                $q->where(function ($sub) {
                    $sub->whereDoesntHave('pembayaran.siswa')
                        ->orWhereHas('pembayaran.siswa', fn ($s) =>
                            $s->where('status_siswa', 'Aktif'));
                })
            );

        $data = $query->get();

        $totalMasuk = 0;
        $totalKeluar = 0;

        $rows = $data->map(function ($item) use (&$totalMasuk, &$totalKeluar) {

            $kategori = $item->kategori?->nama ?? '-';

            if (strtolower($kategori) === 'spp' && $item->tanggal) {
                $bulan = Carbon::parse($item->tanggal)->translatedFormat('F Y');
                $kategori = "SPP ({$bulan})";
            }

            // 🔥 Keterangan
            $ket = '-';

            if ($item->pembayaran?->siswa) {
                $nama = $item->pembayaran->siswa->nama_lengkap ?? '-';
                $kelas = $item->pembayaran->siswa->kelas->nama ?? '-';
                $ket = "{$nama} - {$kelas}";
            } elseif ($item->pembayaran?->tagihan) {
                $t = $item->pembayaran->tagihan;
                $ket = ($t->nama ?? '-') . ' - ' . ($t->kelas_nama ?? '-');
            } else {
                $ket = $item->penanggung_jawab ?? '-';
            }

            if ($item->tipe === 'masuk') {
                $totalMasuk += $item->nominal;
            } else {
                $totalKeluar += $item->nominal;
            }

            return [
                'kode' => $item->kode,
                'tipe' => ucfirst($item->tipe),
                'lembaga' =>
                    $item->pembayaran?->tagihan?->lembaga_nama
                    ?? $item->lembaga?->nama
                    ?? '-',
                'kategori' => $kategori,
                'tanggal' => Carbon::parse($item->tanggal)->format('d-m-Y'),
                'masuk' => $item->tipe === 'masuk' ? $item->nominal : 0,
                'keluar' => $item->tipe === 'keluar' ? $item->nominal : 0,
                'rekening' => $item->rekening
                    ? "{$item->rekening->bank} ({$item->rekening->no_rekening})"
                    : '-',
                'keterangan' => $ket,
            ];
        });

        // HEADER
        $namaLembaga = 'Semua Lembaga';
        if ($filters['lembaga_id']) {
            $lembaga = Lembaga::find($filters['lembaga_id']);
            $namaLembaga = $lembaga?->nama ?? '-';
        }

        $periode = 'All';
        if ($filters['dari'] && $filters['sampai']) {
            $periode =
                Carbon::parse($filters['dari'])->format('d-m-Y') .
                ' s/d ' .
                Carbon::parse($filters['sampai'])->format('d-m-Y');
        }

        return [
            'rows' => $rows,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'saldo' => $totalMasuk - $totalKeluar,
            'namaLembaga' => $namaLembaga,
            'periode' => $periode,
        ];
    }
}