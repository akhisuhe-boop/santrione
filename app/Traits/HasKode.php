<?php

namespace App\Traits;

trait HasKode
{
    public static function generateKode($prefix, $model)
    {
        $year = date('Y');

        $last = $model::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = 1;

        if ($last && $last->kode) {
            $lastNumber = intval(substr($last->kode, -4));
            $number = $lastNumber + 1;
        }

        return $prefix . '-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}