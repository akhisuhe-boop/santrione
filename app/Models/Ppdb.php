<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ppdb extends Model
{
    protected $table = 'ppdbs';
    protected $fillable = [
    'user_id',
    'siswa_id',
    'tahun_ajaran_id',

    'nama_lengkap',
    'nis',
    'nisn',
    'nik',
    'rfid',
    'jenis_kelamin',

    'tempat_lahir',
    'tanggal_lahir',

    'tinggi_badan',
    'berat_badan',
    'golongan_darah',

    'alamat_jalan',
    'provinsi',
    'kabupaten',
    'kecamatan',
    'desa',
    'rt',
    'rw',
    'kode_pos',

    'no_kartu_keluarga',
    'nik_ayah',
    'nama_ayah',
    'status_ayah',
    'pekerjaan_ayah',
    'pendidikan_ayah',
    'penghasilan_ayah',
    'wa_ayah',

    'nik_ibu',
    'nama_ibu',
    'status_ibu',
    'pekerjaan_ibu',
    'pendidikan_ibu',
    'penghasilan_ibu',
    'wa_ibu',

    'nik_wali',
    'nama_wali',
    'status_wali',
    'pekerjaan_wali',
    'pendidikan_wali',
    'penghasilan_wali',
    'hubungan_wali',
    'wa_wali',

    'foto',
    'scan_kk',
    'scan_akta',
    'scan_ijazah',

    'asal_sekolah',

    'lembaga_id',
    'kelas_id',

    'status',
    'password',
];

protected static function booted()
{
    // Sebelum data disimpan
    static::creating(function ($model) {

        if (!$model->tahun_ajaran_id) {
            $model->tahun_ajaran_id = \App\Models\TahunAjaran::aktif()?->id;
        }

    });

    // Setelah data berhasil disimpan
    static::created(function ($ppdb) {

        \App\Services\TagihanService::buatPendaftaran($ppdb);

    });

    // Setelah data diupdate
    static::updated(function ($ppdb) {

        // Jika status berubah menjadi lulus
        if (
            $ppdb->wasChanged('status') &&
            $ppdb->status === 'lulus'
        ) {

            \App\Services\TagihanService::buatDaftarUlang($ppdb);

        }

    });
}

public function toSiswaArray()
{
    return collect($this->attributes)
        ->except(['id', 'status', 'created_at', 'updated_at'])
        ->toArray();
}
public function siswa()
{
    return $this->belongsTo(\App\Models\Siswa::class);
}

public function lembaga(): BelongsTo
{
    return $this->belongsTo(\App\Models\Lembaga::class);
}

public function kelas(): BelongsTo
{
    return $this->belongsTo(\App\Models\Kelas::class);
}

public function tahunAjaran()
{
    return $this->belongsTo(\App\Models\TahunAjaran::class);
}
public function tagihans()
{
    return $this->hasMany(\App\Models\Tagihan::class);
}

public function asrama()
{
    return $this->belongsTo(\App\Models\Asrama::class);
}
}
