<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    use BelongsToTenant;

    // Scoping lewat kelas.lembaga
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('kelas.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [
        'kelas_id',
        'pegawai_id',
        'mata_pelajaran_id',
        'jumlah_jam_per_minggu',
        'jp_per_pertemuan',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }
}