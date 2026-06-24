<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembaga extends Model
{
    protected $fillable = [
        'nama',
        'jenis',
        'kepala_sekolah',
        'logo',
        'yayasan_id',
        'is_tes',
    ];
    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }
    protected $casts = [
    'is_tes' => 'boolean',
    ];

    public function jenisTagihan()
    {
        return $this->hasMany(JenisTagihanLembaga::class);
    }
    
    public function siswas()
    {
        return $this->hasMany(
            \App\Models\Siswa::class
        );
    }
}
