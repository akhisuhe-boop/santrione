<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use BelongsToTenant;

    // Scoping lewat pegawai.lembagas
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('pegawai.lembagas', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [

        'pegawai_id',
        'bulan',
        'tahun',
        'jenis',

        'subtotal',
        'bonus',
        'potongan',
        'total_gaji',

        'status',
        'tanggal_bayar',
        'catatan',
    ];
    
    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function adjustments()
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    public function kas()
    {
        return $this->hasOne(\App\Models\Kas::class);
    }
}