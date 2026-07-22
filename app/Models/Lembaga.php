<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Lembaga extends Model
{
    use BelongsToTenant;

    // Lembaga TIDAK punya relasi lembaga() ke dirinya sendiri — override
    // scope supaya filter langsung ke kolom yayasan_id di tabel ini.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected $fillable = [
    'yayasan_id',
    'nama',
    'jenis',
    'is_tes',
    'kepala_sekolah',
    'bendahara_id',
    'printer_kwitansi',
    'logo',
    'npsn',
    'nss',
    'tarif_pengganti_per_jp',
    ];
    
    protected static function booted()
    {
        static::creating(function ($lembaga) {

            // Isi otomatis dari user yang sedang login (konteks tenant aktif),
            // BUKAN dari yayasan pertama di database.
            // Untuk multi-tenant, yayasan_id WAJIB eksplisit (dari user login
            // atau dari form) sebelum record boleh dibuat.
            if (empty($lembaga->yayasan_id) && auth()->check()) {
                $lembaga->yayasan_id = auth()->user()->yayasan_id;
            }

            if (empty($lembaga->yayasan_id)) {
                throw new \Exception('yayasan_id wajib diisi saat membuat Lembaga baru.');
            }

        });
    }
    
    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }
    
    public function bendahara()
    {
        return $this->belongsTo(Pegawai::class, 'bendahara_id');
    }
    
    protected $casts = [
    'is_tes' => 'boolean',
    ];

    public function jenisTagihan()
    {
        return $this->hasMany(JenisTagihanLembaga::class);
    }
    
    public function siswas()
    {
        return $this->hasMany(
            \App\Models\Siswa::class
        );
    }
}
