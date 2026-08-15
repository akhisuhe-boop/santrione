<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudiKasus extends Model
{
    protected $table = 'studi_kasus';

    protected $fillable = [
        'nama_lembaga', 'badge_text', 'deskripsi', 'foto', 'stats',
        'kutipan', 'kutipan_nama', 'kutipan_jabatan', 'urutan', 'is_active',
    ];

    protected $casts = [
        'stats' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    public function fotoUrl(): ?string
    {
        return $this->foto ? Storage::disk('r2-public')->url($this->foto) : null;
    }
}
