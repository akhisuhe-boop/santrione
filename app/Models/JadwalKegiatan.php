<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class JadwalKegiatan extends Model
{
    use BelongsToTenant;

    // Scoping lewat templateKegiatan.lembaga
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('templateKegiatan.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

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