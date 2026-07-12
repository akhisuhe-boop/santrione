<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamPelajaran extends Model
{
    protected $fillable = [

        'lembaga_id',

        'nama',
        'jam_mulai',
        'jam_selesai',
        'durasi_jp',
        'urutan',
        'aktif',

    ];

    protected $casts = [

        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'aktif' => 'boolean',

    ];

    public function jadwalPelajarans()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }
    
    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}