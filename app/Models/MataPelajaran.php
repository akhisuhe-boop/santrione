<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class MataPelajaran extends Model
{
    use BelongsToTenant;

    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->yayasan_id)) {
                $model->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }
        });
    }

    protected $fillable = [

        'yayasan_id',

        'nama',

        'kompetensi',

    ];

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function jadwalPelajarans()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }
}