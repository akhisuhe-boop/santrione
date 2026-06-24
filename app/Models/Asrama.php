<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pegawai;
use App\Models\Lembaga;
use App\Models\Siswa;

class Asrama extends Model
{
    protected $fillable = [
    'lembaga_id',
    'nama',
    'wali_asrama_id',
    'kapasitas',
    'keterangan',
    ];
    
    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function waliAsrama()
    {
        return $this->belongsTo(Pegawai::class, 'wali_asrama_id');
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}
