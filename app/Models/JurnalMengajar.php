<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pegawai;
use App\Models\PegawaiLembaga;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\JadwalPelajaran;
use App\Models\User;

class JurnalMengajar extends Model
{
    protected $fillable = [
        'pegawai_id',
        'pegawai_lembaga_id',
        'kelas_id',
        'mata_pelajaran_id',
        'jadwal_pelajaran_id',
        'tanggal',
        'jam_ke',
        'durasi_jam',
        'materi',
        'status',
        'validated_by',
        'validated_at',
    ];

    // ======================
    // RELASI
    // ======================

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function jadwal()
    {
        return $this->belongsTo(\App\Models\JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function absensiMapels()
    {
        return $this->hasMany(AbsensiMapel::class);
    }

    public function pegawaiLembaga()
    {
        return $this->belongsTo(PegawaiLembaga::class);
    }
}