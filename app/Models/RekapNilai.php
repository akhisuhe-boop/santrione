<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapNilai extends Model
{
    protected $fillable = [

        'siswa_id',
        'kelas_id',
        'mapel_id',
        'guru_id',
        'tahun_ajaran_id',

        'nilai_akhir',
        'grade',
        'deskripsi',
        'status',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function siswa()
    {
        return $this->belongsTo(
            Siswa::class
        );
    }

    public function kelas()
    {
        return $this->belongsTo(
            Kelas::class
        );
    }

    public function mapel()
    {
        return $this->belongsTo(
            MataPelajaran::class,
            'mapel_id'
        );
    }

    public function guru()
    {
        return $this->belongsTo(
            Pegawai::class,
            'guru_id'
        );
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(
            TahunAjaran::class
        );
    }
}