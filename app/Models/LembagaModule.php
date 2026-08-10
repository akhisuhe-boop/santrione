<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LembagaModule extends Model
{
    protected $fillable = [
        'lembaga_id',
        'module_price_id',
        'is_active',
        'aktif_sejak',
        'nonaktif_sejak',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'aktif_sejak' => 'datetime',
            'nonaktif_sejak' => 'datetime',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function modulePrice()
    {
        return $this->belongsTo(ModulePrice::class);
    }
}
