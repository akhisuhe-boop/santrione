<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
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
}
