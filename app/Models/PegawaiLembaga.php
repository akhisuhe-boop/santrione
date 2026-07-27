<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use App\Models\Lembaga;

class PegawaiLembaga extends Model
{
    use BelongsToTenant;

    protected $table = 'pegawai_lembaga';

    /**
     * Sebelumnya scoping ikut default trait (lewat whereHas('lembaga',
     * ...)) — itu bikin baris penugasan pesantren (lembaga_id kosong)
     * ke-saring habis SEBELUM sempat ditampilkan dimanapun, walau
     * kolom tabelnya sendiri sudah benar. Sekarang scoping lewat
     * yayasan_id milik pegawai-nya langsung (selalu terisi).
     */
    protected static function applyTenantScope(\Illuminate\Database\Eloquent\Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('pegawai', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [

        'pegawai_id',
        'lembaga_id',

        'jabatan',
        'status',

        'metode_penggajian',
        'nominal_tetap',
        'tarif_per_jp',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}