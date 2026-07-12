<?php

namespace App\Services;

class NilaiService
{
    /*
    |--------------------------------------------------------------------------
    | HITUNG NILAI AKHIR
    |--------------------------------------------------------------------------
    */

    public static function hitungNilaiAkhir(
        $tugas,
        $harian,
        $uts,
        $uas
    ) {

        return round(

            (($tugas ?? 0) * 0.20) +
            (($harian ?? 0) * 0.30) +
            (($uts ?? 0) * 0.20) +
            (($uas ?? 0) * 0.30)

        );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE GRADE
    |--------------------------------------------------------------------------
    */

    public static function generateGrade($nilai): string
    {
        if ($nilai >= 95) return 'A+';
        if ($nilai >= 90) return 'A';
        if ($nilai >= 85) return 'A-';
        if ($nilai >= 80) return 'B+';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 70) return 'B-';
        if ($nilai >= 65) return 'C+';
        if ($nilai >= 60) return 'C';

        return 'D';
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE DESKRIPSI
    |--------------------------------------------------------------------------
    */

    public static function generateDeskripsi(
    int $nilai,
    ?string $kompetensi = null
    ): string {
    
        $kompetensi = $kompetensi ?: 'materi pembelajaran';
    
        if ($nilai >= 90) {
    
            return "Menunjukkan capaian belajar yang sangat baik dalam {$kompetensi}, mampu menerapkan konsep dengan tepat serta aktif selama proses pembelajaran.";
    
        }
    
        if ($nilai >= 80) {
    
            return "Menunjukkan pemahaman yang baik terhadap {$kompetensi} serta mampu menyelesaikan sebagian besar kegiatan pembelajaran dengan baik.";
    
        }
    
        if ($nilai >= 70) {
    
            return "Menunjukkan perkembangan yang cukup baik dalam memahami {$kompetensi}, namun masih perlu meningkatkan konsistensi belajar.";
    
        }
    
        return "Perlu meningkatkan pemahaman terhadap {$kompetensi} melalui latihan dan pendampingan yang lebih intensif.";
    }
}