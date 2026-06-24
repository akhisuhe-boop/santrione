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
        $nilai
    ): string {

        if ($nilai >= 90) {

            return 'Menunjukkan capaian belajar yang sangat baik serta aktif dalam proses pembelajaran.';

        } elseif ($nilai >= 80) {

            return 'Menunjukkan pemahaman yang baik terhadap materi pembelajaran dan memiliki semangat belajar yang baik.';

        } elseif ($nilai >= 70) {

            return 'Menunjukkan perkembangan belajar yang cukup baik dan perlu meningkatkan konsistensi belajar.';

        }

        return 'Memerlukan pendampingan lebih lanjut dalam proses pembelajaran.';
    }
}