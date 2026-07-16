<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Absensi extends Model
{
    use BelongsToTenant;

    // Absensi bisa untuk siswa ATAU pegawai (salah satu kolom nullable),
    // jadi scoping-nya OR lewat dua jalur berbeda.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where(function (Builder $q) use ($yayasanId) {
            $q->whereHas('siswa.lembaga', fn ($sub) => $sub->where('yayasan_id', $yayasanId))
              ->orWhereHas('pegawai.lembagas', fn ($sub) => $sub->where('yayasan_id', $yayasanId));
        });
    }

    protected $table = 'absensi';

    protected $fillable = [
        'jadwal_kegiatan_id',
        'siswa_id',
        'pegawai_id',
        'tipe',
        'status',
        'metode',
        'jam_scan',
        'waktu'
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function jadwalKegiatan()
    {
        return $this->belongsTo(
            JadwalKegiatan::class,
            'jadwal_kegiatan_id'
        );
    }
}