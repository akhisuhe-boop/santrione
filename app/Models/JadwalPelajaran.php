<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    use BelongsToTenant;

    // Scoping lewat kelas.lembaga
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('kelas.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $table = 'jadwal_pelajarans';

    protected $fillable = [
        'pegawai_id',
        'kelas_id',
        'hari',
        'mata_pelajaran_id',
        'jam_pelajaran_id',
    ];

    public function guru()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function jamPelajaran()
    {
        return $this->belongsTo(JamPelajaran::class);
    }

    public function absensiMapels()
    {
        return $this->hasMany(AbsensiMapel::class);
    }
}