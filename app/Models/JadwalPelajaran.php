<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{
    protected $table = 'jadwal_pelajarans'; // 🔥 INI WAJIB

    protected $fillable = [
        'pegawai_id',
        'kelas_id',
        'hari',
        'jam_ke',
        'durasi_jam',
        'mata_pelajaran_id',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
    
    public function mataPelajaran()
    {
        return $this->belongsTo(\App\Models\MataPelajaran::class);
    }

    public function absensiMapels()
    {
        return $this->hasMany(AbsensiMapel::class);
    }

    public function guru()
    {
        return $this->belongsTo(\App\Models\Pegawai::class, 'pegawai_id');
    }

}