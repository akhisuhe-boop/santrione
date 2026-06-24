<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaportNonAkademik extends Model
{
    protected $table = 'raport_non_akademiks';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tahun_ajaran_id',
        'semester',
        'catatan_wali_kelas',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function kepribadians()
    {
        return $this->hasMany(RaportKepribadian::class);
    }

    public function ekstrakurikulers()
    {
        return $this->hasMany(RaportEkstrakurikuler::class);
    }
}