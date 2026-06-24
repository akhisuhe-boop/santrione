<?php

namespace App\Services;

use App\Models\Siswa;

class NisService
{
    public static function generate($lembagaId)
    {
        $tahunMasuk = now()->format('y') . now()->addYear()->format('y');

        $kodeLembaga = str_pad($lembagaId, 2, '0', STR_PAD_LEFT);

        $last = Siswa::where('lembaga_id', $lembagaId)
            ->where('nis', 'like', $tahunMasuk . $kodeLembaga . '%')
            ->orderBy('nis', 'desc')
            ->first();

        $next = $last ? ((int) substr($last->nis, -4) + 1) : 1;

        return $tahunMasuk . $kodeLembaga . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}