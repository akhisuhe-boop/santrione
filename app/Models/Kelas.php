<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use BelongsToTenant;

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

  public function kurikulums()
    {
        return $this->hasMany(Kurikulum::class);
    }
    public function jadwalPelajarans()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }
}
