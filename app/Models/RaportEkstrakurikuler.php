<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class RaportEkstrakurikuler extends Model
{
    use BelongsToTenant;

    // Scoping lewat raport.siswa.lembaga
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('raport.siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $table = 'raport_ekstrakurikulers';

    protected $fillable = [
        'raport_non_akademik_id',
        'nama_ekskul',
        'nilai',
        'grade',
        'deskripsi',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function raport()
    {
        return $this->belongsTo(RaportNonAkademik::class);
    }
}