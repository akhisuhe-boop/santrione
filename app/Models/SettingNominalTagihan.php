<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SettingNominalTagihan extends Model
{
    use BelongsToTenant;

    protected $table = 'setting_nominal_tagihans';

    protected $fillable = [
        'jenis_tagihan_id',
        'tahun_ajaran_id',
        'lembaga_id', // 🔥 WAJIB
        'kelas_id',
        'siswa_id',
        'bulan',
        'nominal',
    ];

    // ======================
    // 🔗 RELATION
    // ======================

    public function jenisTagihan()
    {
        return $this->belongsTo(\App\Models\JenisTagihan::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(\App\Models\TahunAjaran::class);
    }

    public function lembaga()
    {
        return $this->belongsTo(\App\Models\Lembaga::class);
    }

    public function kelas()
    {
        return $this->belongsTo(\App\Models\Kelas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(\App\Models\Siswa::class);
    }

    // ======================
    // 🔍 SCOPE FILTER
    // ======================
    public function scopeByFilter($query, $jenisId, $tahunId)
    {
        return $query
            ->where('jenis_tagihan_id', $jenisId)
            ->where('tahun_ajaran_id', $tahunId);
    }

    // ======================
    // 🔄 CAST
    // ======================
    protected $casts = [
        'bulan' => 'array',
    ];

    // ======================
    // 🔥 AUTO FORMAT BULAN
    // ======================
    protected function setBulanAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['bulan'] = json_encode(array_values($value));
        } else {
            $this->attributes['bulan'] = json_encode([$value]);
        }
    }
}