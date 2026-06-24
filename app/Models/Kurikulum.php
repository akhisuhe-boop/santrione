<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    protected $fillable = [
        'kelas_id',
        'pegawai_id',
        'mata_pelajaran_id',
        'jumlah_jam_per_minggu',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }
}