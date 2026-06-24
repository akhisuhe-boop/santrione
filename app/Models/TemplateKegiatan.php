<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateKegiatan extends Model
{
    protected $table = 'template_kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'tipe',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'toleransi_telat',
        'metode_scan',
        'aktif'
    ];
}