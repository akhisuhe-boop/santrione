<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;

class Nilai extends Model
{
    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'mapel_id',
        'guru_id',
        'tahun_ajaran_id',
        'tipe_nilai',
        'judul',
        'nilai',
        'catatan',
        'is_publish',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}