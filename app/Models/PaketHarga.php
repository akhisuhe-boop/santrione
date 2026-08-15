<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaketHarga extends Model
{
    protected $fillable = [
        'nama', 'tagline', 'target_pasar', 'harga_bulanan', 'diskon_tahunan_persen',
        'is_recommended', 'fitur', 'cta_text', 'urutan', 'is_active',
    ];

    protected $casts = [
        'fitur' => 'array',
        'is_recommended' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    public function hargaTahunanPerBulan(): int
    {
        return (int) round($this->harga_bulanan * (100 - $this->diskon_tahunan_persen) / 100);
    }

    public function totalTahunan(): int
    {
        return $this->hargaTahunanPerBulan() * 12;
    }
}
