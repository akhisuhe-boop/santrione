<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModulAplikasi extends Model
{
    protected $table = 'modul_aplikasis';

    protected $fillable = ['icon', 'judul', 'deskripsi', 'tag_text', 'is_featured', 'urutan', 'is_active'];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }
}
