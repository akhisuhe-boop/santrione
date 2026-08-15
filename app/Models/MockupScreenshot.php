<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MockupScreenshot extends Model
{
    protected $fillable = ['judul', 'deskripsi', 'gambar', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    public function url(): string
    {
        return Storage::disk('r2-public')->url($this->gambar);
    }
}
