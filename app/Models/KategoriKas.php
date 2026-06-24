<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KategoriKas extends Model
{
    protected $fillable = [
        'nama',
        'tipe',
        'is_active',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode)) {
                $model->kode = self::generateKode($model->nama, $model->tipe);
            }
        });
    }

    protected static function generateKode($nama, $tipe)
    {
        $slug = Str::slug($nama, '_');

        $kode = $tipe . '_' . $slug;

        $original = $kode;
        $i = 1;

        while (self::where('kode', $kode)->exists()) {
            $kode = $original . '_' . $i++;
        }

        return $kode;
    }
}
