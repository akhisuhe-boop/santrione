<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TemplateKegiatan extends Model
{
    use BelongsToTenant;

    protected $table = 'template_kegiatan';

    protected $fillable = [
        'lembaga_id',
        'nama_kegiatan',
        'tipe',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'toleransi_telat',
        'metode_scan',
        'aktif'
    ];

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}