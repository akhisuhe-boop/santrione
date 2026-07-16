<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TahfidzTarget extends Model
{
    use BelongsToTenant;

    // Scoping lewat siswa.lembaga (tidak ada lembaga_id langsung)
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = ['siswa_id', 'target_juz'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}