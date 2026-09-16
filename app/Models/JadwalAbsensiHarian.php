<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalAbsensiHarian extends Model
{
    protected $fillable = [
        'lembaga_id',
        'hari',
        'jam_masuk_siswa',
        'jam_pulang_siswa',
        'jam_masuk_guru',
        'jam_pulang_guru',
        'toleransi_telat_menit',
    ];

    public const HARI = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
}
