<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PayrollAdjustmentTemplate extends Model
{
    use BelongsToTenant;

    // Scoping lewat pegawai.lembagas
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('pegawai.lembagas', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [
        'pegawai_id',
        'tipe',
        'nama_komponen',
        'nominal',
        'is_active',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
