<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
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