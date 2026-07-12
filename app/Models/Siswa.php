<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash; // jangan lupa import Hash
use App\Models\Wallet;

class Siswa extends Model
{
    protected $fillable = [
        'lembaga_id',
        'kelas_id',
        'asrama_id',
        'asal_sekolah',
        'rfid',
        'nis',
        'nisn',
        'nik',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'tinggi_badan',
        'berat_badan',
        'golongan_darah',
        'alamat_jalan',
        'rt',
        'rw',
        'provinsi',
        'kabupaten',
        'kecamatan',
        'desa',
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
        'hubungan_wali',
        'status_wali',
        'pekerjaan_wali',
        'pendidikan_wali',
        'penghasilan_wali',
        'wa_wali',
        'foto',
        'scan_kk',
        'scan_akta',
        'scan_ijazah',
        'status_siswa',
        'tanggal_lulus',
        'tanggal_pindah',
        'password',
        'pin',
    ];

    protected $casts = [
    'password' => 'hashed',
        //'pin' => 'hashed', // tetap seperti punya Akhi (tidak diubah)
    ];

    protected static function booted()
    {
        // 🔹 Saat create (default password & PIN)
        static::creating(function ($siswa) {

            if (!$siswa->password) {
                $siswa->password = '12345678';
            }
        
            if (!$siswa->pin) {
                $siswa->pin = '123456';
            }
        });

        // 🔹 Setelah berhasil dibuat → auto wallet
        static::created(function ($siswa) {
            Wallet::create([
                'siswa_id' => $siswa->id,
                'saldo' => 0,
            ]);
        });
    }

    // Relasi ke lembaga
    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

    // Relasi ke kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    // Relasi ke KartuTemplate
    public function template()
    {
        return $this->belongsTo(KartuTemplate::class,'kartu_template_id');
    }

    // Relasi ke absensi
    public function absensis()
    {
    return $this->hasMany(Absensi::class);
    }

    // Relasi ke PelanggaranSiswa
    public function pelanggaranSiswa()
    {
    return $this->hasMany(PelanggaranSiswa::class);
    }

    // Relasi ke PrestasiSiswa
    public function prestasiSiswa()
    {
    return $this->hasMany(PrestasiSiswa::class);
    }

    // Relasi ke TahfidzSetoran
    public function tahfidzSetoran()
    {
    return $this->hasMany(TahfidzSetoran::class);
    }

    // Relasi ke TahfidzTarget
    public function targetTahfidz()
    {
        return $this->hasOne(TahfidzTarget::class);
    }

    // Relasi ke AbsensiMapel
    public function absensiMapels()
    {
        return $this->hasMany(AbsensiMapel::class);
    }

    public function getProgressTahfidzAttribute()
{
    $target = $this->targetTahfidz;

    if (!$target) {
        return [
            'juz' => '-',
            'progress' => 0,
            'status' => '-',
        ];
    }

    // 🔥 Ambil data juz dari tabel juzs
    $juz = \App\Models\Juz::where('nama', 'Juz ' . $target->target_juz)->first();

    if (!$juz) {
        return [
            'juz' => 'Juz ' . $target->target_juz,
            'progress' => 0,
            'status' => '-',
        ];
    }

    $totalAyatJuz = $juz->total_ayat;

    $hafal = $this->tahfidzSetoran()
        ->where('jenis', 'ziyadah')
        ->where('juz_id', $juz->id)
        ->sum('jumlah_ayat');

    $progress = $totalAyatJuz > 0
        ? round(($hafal / $totalAyatJuz) * 100, 1)
        : 0;

    if ($progress >= 100) $status = 'Tercapai';
    elseif ($progress >= 70) $status = 'Hampir Selesai';
    elseif ($progress >= 40) $status = 'Perlu Perhatian';
    else $status = 'Perlu Percepatan';

    return [
        'juz' => 'Juz ' . $target->target_juz,
        'progress' => $progress,
        'status' => $status,
    ];
}

    public function getStatusJuzAttribute()
    {
        $progress = $this->progress_juz;

        if ($progress >= 100) return 'Tercapai';
        if ($progress >= 70) return 'Hampir Selesai';
        if ($progress >= 40) return 'Perlu Perhatian';
        return 'Perlu Percepatan';
    }

    public function perizinans()
    {
        return $this->hasMany(\App\Models\Perizinan::class);
    }

    //Relasi ke Tagihan
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class);
    }

    //Relasi ke Wallet
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    //relasi ke ppdb
    public function ppdb()
    {
        return $this->hasOne(\App\Models\Ppdb::class);
    }

    // Relasi ke Asrama
    public function asrama()
    {
        return $this->belongsTo(Asrama::class);
    }

    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }

    public function raportNonAkademik()
    {
        return $this->hasMany(RaportNonAkademik::class);
    }
}