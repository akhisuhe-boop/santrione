<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RekapNilai extends Model
{
    use BelongsToTenant;

    // Scoping lewat siswa.lembaga (tidak ada lembaga_id langsung)
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [

        'siswa_id',
        'kelas_id',
        'mapel_id',
        'guru_id',
        'tahun_ajaran_id',

        'nilai_akhir',
        'grade',
        'deskripsi',
        'status',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function siswa()
    {
        return $this->belongsTo(
            Siswa::class
        );
    }

    public function kelas()
    {
        return $this->belongsTo(
            Kelas::class
        );
    }

    public function mapel()
    {
        return $this->belongsTo(
            MataPelajaran::class,
            'mapel_id'
        );
    }

    public function guru()
    {
        return $this->belongsTo(
            Pegawai::class,
            'guru_id'
        );
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(
            TahunAjaran::class
        );
    }
}