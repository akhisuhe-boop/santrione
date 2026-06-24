<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKegiatan extends Model
{
    protected $table = 'jadwal_kegiatan';

    protected $fillable = [
        'template_kegiatan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai'
    ];

    // relasi lama
    public function template()
    {
        return $this->belongsTo(TemplateKegiatan::class,'template_kegiatan_id');
    }

    // alias relasi supaya controller lama tetap jalan
    public function templateKegiatan()
    {
        return $this->belongsTo(TemplateKegiatan::class,'template_kegiatan_id');
    }
}