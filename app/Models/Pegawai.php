<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use App\Models\Concerns\BelongsToTenant;

class Pegawai extends Authenticatable
{
    use Notifiable, BelongsToTenant;

    // Sebelumnya scoping lewat whereHas('lembagas', ...) -- tapi itu
    // bikin pegawai level pesantren (tidak terikat 1 lembaga
    // spesifik, lembaga_id kosong) jadi tidak kelihatan oleh yayasan
    // manapun. Sekarang scoping langsung lewat kolom yayasan_id.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected static function booted(): void
    {
        static::creating(function (Pegawai $pegawai) {

            // Jika password belum diisi, gunakan NIY sebagai password default
            if (empty($pegawai->password) && !empty($pegawai->niy)) {
                $pegawai->password = Hash::make($pegawai->niy);
            }

            if (empty($pegawai->yayasan_id)) {
                $pegawai->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }

        });
    }
    
    protected $hidden = [
    'password',
    'remember_token',
    ];
    
    protected $fillable = [
    'yayasan_id',
    'nama',
    'niy',
    'nik',
    'jenis_kelamin',
    'no_hp',
    'email',
    'alamat',
    'pendidikan',
    'universitas',
    'golongan',
    'tanggal_masuk',
    'foto',
    'file_ijazah',
    'is_active',
    'qr_code',
    'rfid',
    'password',
    ];

    public function lembagas()
    {
        return $this->belongsToMany(Lembaga::class, 'pegawai_lembaga')
            ->withPivot(['jabatan','status', 'metode_penggajian', 'nominal_tetap', 'tarif_per_jp'])
            ->withTimestamps();
    }

    // Relasi ke TahfidzSetoran
    public function tahfidzSetoran()
    {
        return $this->hasMany(TahfidzSetoran::class);
    }

    // Relasi ke AbsensiHarian (absensi masuk & pulang)
    public function absensiHarians()
    {
        return $this->hasMany(AbsensiHarian::class)->latest('tanggal');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function pegawaiLembagas()
    {
        return $this->hasMany(PegawaiLembaga::class);
    }
    
    public function jadwalPelajarans()
    {
        return $this->hasMany(JadwalPelajaran::class, 'pegawai_id');
    }
}
