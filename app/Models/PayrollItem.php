<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class PayrollItem extends Model
{
    use BelongsToTenant;

    // Tidak ada lembaga_id langsung — scoping lewat pegawaiLembaga.lembaga
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('pegawaiLembaga.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [

        'payroll_id',
        'pegawai_lembaga_id',

        'nama_komponen',
        'jenis',

        'qty',
        'tarif',
        'subtotal',

        'keterangan',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function pegawaiLembaga()
    {
        return $this->belongsTo(PegawaiLembaga::class);
    }
}