<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    protected $fillable = ['nama', 'jabatan', 'asal_pesantren', 'isi', 'rating', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    public function inisial(): string
    {
        $kata = preg_split('/\s+/', trim($this->nama));

        return mb_strtoupper(mb_substr($kata[0] ?? '', 0, 1).mb_substr($kata[1] ?? '', 0, 1));
    }
}
