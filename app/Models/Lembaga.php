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
    'jam_masuk_siswa',
    'jam_pulang_siswa',
    'jam_masuk_guru',
    'jam_pulang_guru',
    'toleransi_telat_menit',
    'max_jp_kelas_per_minggu',
    'max_jp_guru_per_minggu',
    'warning_jp_guru_per_minggu',
    ];
    
    protected static function booted()
    {
        static::creating(function ($lembaga) {

            // Isi otomatis dari TENANT YANG SEDANG AKTIF di panel (bukan
            // langsung dari auth()->user()->yayasan_id) — soalnya Super
            // Admin platform (is_platform_admin) yayasan_id akunnya
            // sendiri kosong, dia kerja lewat tenant yang lagi
            // diimpersonate/dipilih. Untuk user biasa (bukan platform
            // admin), Filament::getTenant() pada dasarnya sama saja
            // dengan yayasan_id mereka sendiri.
            if (empty($lembaga->yayasan_id)) {
                $lembaga->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
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
