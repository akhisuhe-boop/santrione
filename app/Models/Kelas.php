<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
  protected $fillable = [
        'lembaga_id',
        'nama',
    ];
  public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

  public function waliKelas()
    {
        return $this->belongsTo(Pegawai::class, 'wali_kelas_id');
    }

  public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

  public function kurikulum()
    {
        return $this->hasOne(Kurikulum::class);
    }
}
