<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Pegawai extends Authenticatable
{
    use Notifiable;

    protected static function booted(): void
    {
        static::creating(function (Pegawai $pegawai) {

            // Jika password belum diisi, gunakan NIY sebagai password default
            if (empty($pegawai->password) && !empty($pegawai->niy)) {
                $pegawai->password = Hash::make($pegawai->niy);
            }

        });
    }
    
    protected $hidden = [
    'password',
    'remember_token',
    ];
    
    protected $fillable = [
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
