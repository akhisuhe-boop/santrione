<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FaqItem extends Model
{
    protected $fillable = ['pertanyaan', 'jawaban', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }
}
