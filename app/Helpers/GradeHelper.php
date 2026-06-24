<?php

namespace App\Helpers;

class GradeHelper
{
    public static function generate($nilai): string
    {
        if ($nilai >= 95) return 'A+';
        if ($nilai >= 90) return 'A';
        if ($nilai >= 85) return 'A-';
        if ($nilai >= 80) return 'B+';
        if ($nilai >= 75) return 'B';
        if ($nilai >= 70) return 'B-';
        if ($nilai >= 65) return 'C+';
        if ($nilai >= 60) return 'C';
        if ($nilai >= 50) return 'D';

        return 'E';
    }
}