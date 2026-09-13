<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DITAMBAHKAN -- tier diskon berdasar TOTAL SISWA se-Yayasan.
 * siswa_max null = tidak terbatas ke atas.
 */
class DiskonVolumeSiswa extends Model
{
    protected $fillable = [
        'siswa_min',
        'siswa_max',
        'diskon_persen',
        'urutan',
    ];

    /**
     * Cari persen diskon yang berlaku untuk $totalSiswa, atau 0 kalau
     * tidak ada tier yang cocok (mis. tabel belum diisi sama sekali).
     */
    public static function persenUntuk(int $totalSiswa): int
    {
        $tier = static::query()
            ->where('siswa_min', '<=', $totalSiswa)
            ->where(function ($q) use ($totalSiswa) {
                $q->whereNull('siswa_max')->orWhere('siswa_max', '>=', $totalSiswa);
            })
            ->orderByDesc('siswa_min')
            ->first();

        return (int) ($tier->diskon_persen ?? 0);
    }
}
