<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class AbsensiHarian extends Model
{
    use BelongsToTenant;

    // Sama seperti Absensi (kegiatan): siswa_id / pegawai_id salah satu
    // nullable, jadi scoping tenant-nya OR lewat dua jalur relasi.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where(function (Builder $q) use ($yayasanId) {
            $q->whereHas('siswa.lembaga', fn ($sub) => $sub->where('yayasan_id', $yayasanId))
              ->orWhereHas('pegawai.lembagas', fn ($sub) => $sub->where('yayasan_id', $yayasanId));
        });
    }

    protected $table = 'absensi_harians';

    protected $fillable = [
        'tanggal',
        'siswa_id',
        'pegawai_id',
        'tipe',
        'jam_masuk',
        'status_masuk',
        'metode_masuk',
        'jam_pulang',
        'status_pulang',
        'metode_pulang',
        'keterangan',
        'diinput_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function diinputOleh()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }
}
