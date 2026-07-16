<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class TahunAjaran extends Model
{
    use BelongsToTenant;

    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected $fillable = [
        'yayasan_id',
        'nama',
        'semester',
        'aktif',
    ];

    protected $casts = [
    'aktif' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->yayasan_id)) {
                $model->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }
        });

        static::saving(function ($model) {
            if ($model->aktif) {
                // Hanya nonaktifkan tahun ajaran lain DALAM YAYASAN YANG SAMA
                // — bukan seluruh sistem, supaya tidak mempengaruhi yayasan lain.
                self::withoutGlobalScopes()
                    ->where('yayasan_id', $model->yayasan_id)
                    ->where('aktif', true)
                    ->where('id', '!=', $model->id)
                    ->update(['aktif' => false]);
            }
        });
    }

    public static function aktif()
    {
        return self::where('aktif', true)->first();
    }

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function ppdbs()
    {
        return $this->hasMany(\App\Models\Ppdb::class);
    }
    
}
