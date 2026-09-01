<?php

namespace App\Services;

use App\Models\KantinTransaksi;
use App\Models\Lembaga;
use Carbon\Carbon;

class LaporanKantinPdf
{
    public static function getData(array $filters): array
    {
        $query = KantinTransaksi::query()
            ->with(['siswa', 'pegawai', 'lembaga', 'items'])
            ->when($filters['dari'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '>=', $v))
            ->when($filters['sampai'] ?? null, fn ($q, $v) => $q->whereDate('tanggal', '<=', $v))
            ->when($filters['lembaga_id'] ?? null, fn ($q, $v) => $q->where('lembaga_id', $v))
            ->when($filters['metode'] ?? null, fn ($q, $v) => $q->where('metode', $v))
            ->when($filters['diinput_oleh'] ?? null, fn ($q, $v) => $q->where('diinput_oleh', $v))
            ->orderByDesc('tanggal');

        $rows = $query->get();

        $totalTransaksi = $rows->count();
        $totalOmzet = (int) $rows->sum('total');
        $walletRows = $rows->where('metode', 'wallet');
        $tunaiRows = $rows->where('metode', 'tunai');

        $items = $rows->map(function ($trx) {
            return [
                'kode' => $trx->kode,
                'tanggal' => $trx->tanggal ? Carbon::parse($trx->tanggal)->translatedFormat('d-m-Y H:i') : '-',
                'pembeli' => $trx->siswa?->nama_lengkap ?? $trx->pegawai?->nama ?? 'Umum (Pengunjung)',
                'tipe' => $trx->siswa ? 'Siswa' : ($trx->pegawai ? 'Guru/Staf' : 'Pengunjung'),
                'lembaga' => ($trx->siswa || $trx->pegawai) ? ($trx->lembaga?->nama ?? '-') : '-',
                'metode' => ucfirst($trx->metode),
                'item' => $trx->items->pluck('nama_produk')->implode(', '),
                'total' => $trx->total,
                'kasir' => $trx->diinput_oleh ?? '-',
            ];
        });

        return [
            'items' => $items,
            'lembagaNama' => filled($filters['lembaga_id'] ?? null)
                ? Lembaga::find($filters['lembaga_id'])?->nama
                : 'Semua Lembaga',
            'periode' => (filled($filters['dari'] ?? null) || filled($filters['sampai'] ?? null))
                ? ($filters['dari'] ?? '...') . ' s/d ' . ($filters['sampai'] ?? '...')
                : 'Semua Periode',
            'totalTransaksi' => $totalTransaksi,
            'totalOmzet' => $totalOmzet,
            'walletCount' => $walletRows->count(),
            'walletTotal' => (int) $walletRows->sum('total'),
            'tunaiCount' => $tunaiRows->count(),
            'tunaiTotal' => (int) $tunaiRows->sum('total'),
            'rasioTunaiPersen' => $totalTransaksi > 0
                ? round(($tunaiRows->count() / $totalTransaksi) * 100, 1)
                : 0,
        ];
    }
}
