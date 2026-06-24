<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $fillable = [
        'nama',
        'semester',
        'aktif',
    ];

    protected $casts = [
    'aktif' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->aktif) {
                self::where('aktif', true)
                    ->where('id', '!=', $model->id)
                    ->update(['aktif' => false]);
            }
        });
    }

    public static function aktif()
    {
        return self::where('aktif', true)->first();
    }

    public function ppdbs()
    {
        return $this->hasMany(\App\Models\Ppdb::class);
    }
    
}
