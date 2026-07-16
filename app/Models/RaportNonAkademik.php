<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RaportNonAkademik extends Model
{
    use BelongsToTenant;

    // Scoping lewat siswa.lembaga (tidak ada lembaga_id langsung)
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $table = 'raport_non_akademiks';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tahun_ajaran_id',
        'semester',
        'catatan_wali_kelas',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function kepribadians()
    {
        return $this->hasMany(RaportKepribadian::class);
    }

    public function ekstrakurikulers()
    {
        return $this->hasMany(RaportEkstrakurikuler::class);
    }
}