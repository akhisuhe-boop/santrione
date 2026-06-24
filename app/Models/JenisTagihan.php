<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JenisTagihan extends Model
{
    protected $table = 'jenis_tagihans';

    protected $fillable = [
        'nama',
        'kode',
        'default_nominal',
        'is_bulanan',
        'is_cicilan',
        'kategori_kas_id',
    ];

    protected $casts = [
        'is_bulanan' => 'boolean',
        'is_cicilan' => 'boolean',
    ];

    // ======================
    // 🔥 AUTO LOGIC
    // ======================
    protected static function booted()
    {
        static::saving(function ($model) {

            // 🔥 AUTO GENERATE KODE (jika kosong)
            if (!$model->kode && $model->nama) {
                $model->kode = strtolower(Str::slug($model->nama, '_'));
            }

            // 🔥 RULE: bulanan tidak boleh cicilan
            if ($model->is_bulanan) {
                $model->is_cicilan = false;
            }
        });
    }

    // ======================
    // 🔗 RELATION
    // ======================

    // ke tagihan
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }

    // ke kategori kas
    public function kategoriKas()
    {
        return $this->belongsTo(\App\Models\KategoriKas::class);
    }

    // ke setting nominal tagihan
    public function settingNominalTagihans()
    {
        return $this->hasMany(\App\Models\SettingNominalTagihan::class);
    }
}