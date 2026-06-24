<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaportEkstrakurikuler extends Model
{
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