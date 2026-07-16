<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Perizinan extends Model
{
    use BelongsToTenant;

    // Scoping lewat siswa.lembaga (tidak ada lembaga_id langsung)
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [
        'siswa_id',
        'tipe',
        'tanggal_mulai',
        'tanggal_selesai',
        'keperluan',
        'penjemput',
        'hubungan',
        'no_wa',
        'status',
        'waktu_keluar',
        'waktu_kembali',
        'keterangan_waktu',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    protected static function booted()
{
    static::saving(function ($model) {
        if ($model->tanggal_selesai) {
            $model->tanggal_selesai = \Carbon\Carbon::parse($model->tanggal_selesai)
                ->setTime(17, 0, 0);
        }
    });
}
}