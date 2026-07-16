<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TahfidzSetoran extends Model
{
    use BelongsToTenant;

    // Scoping lewat siswa.lembaga (tidak ada lembaga_id langsung)
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [
        'siswa_id',
        'pegawai_id',
        'tanggal',
        'jenis',
        'surah_id',
        'juz_id',
        'ayat_dari',
        'ayat_sampai',
        'jumlah_ayat',
        'nilai',
        'catatan',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->ayat_dari && $model->ayat_sampai) {
                $model->jumlah_ayat = ($model->ayat_sampai - $model->ayat_dari) + 1;
            }
        });
    }

    // RELASI
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function surah()
    {
        return $this->belongsTo(Surah::class);
    }

    public function juz()
    {
        return $this->belongsTo(Juz::class);
    }
}