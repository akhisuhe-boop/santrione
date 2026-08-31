<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Lembaga extends Model
{
    use BelongsToTenant;

    // Lembaga TIDAK punya relasi lembaga() ke dirinya sendiri — override
    // scope supaya filter langsung ke kolom yayasan_id di tabel ini.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected $fillable = [
    'yayasan_id',
    'nama',
    'jenis',
    'is_tes',
    'kepala_sekolah',
    'bendahara_id',
    'printer_kwitansi',
    'logo',
    'npsn',
    'nss',
    'tarif_pengganti_per_jp',
    'jam_masuk_siswa',
    'jam_pulang_siswa',
    'jam_masuk_guru',
    'jam_pulang_guru',
    'toleransi_telat_menit',
    'max_jp_kelas_per_minggu',
    'max_jp_guru_per_minggu',
    'warning_jp_guru_per_minggu',
    'jumlah_siswa_billing',
    'siswa_billing_snapshot_at',
    'urutan_billing',
    'payment_gateway',
    'xendit_account_holder_id',
    'xendit_status',
    'doku_sub_account_id',
    'doku_account_no',
    'doku_split_rule_id',
    'doku_status',
    ];
    
    protected static function booted()
    {
        static::creating(function ($lembaga) {

            // Isi otomatis dari TENANT YANG SEDANG AKTIF di panel (bukan
            // langsung dari auth()->user()->yayasan_id) — soalnya Super
            // Admin platform (is_platform_admin) yayasan_id akunnya
            // sendiri kosong, dia kerja lewat tenant yang lagi
            // diimpersonate/dipilih. Untuk user biasa (bukan platform
            // admin), Filament::getTenant() pada dasarnya sama saja
            // dengan yayasan_id mereka sendiri.
            if (empty($lembaga->yayasan_id)) {
                $lembaga->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }

            if (empty($lembaga->yayasan_id)) {
                throw new \Exception('yayasan_id wajib diisi saat membuat Lembaga baru.');
            }

        });
    }
    
    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }
    
    public function bendahara()
    {
        return $this->belongsTo(Pegawai::class, 'bendahara_id');
    }
    
    protected $casts = [
    'is_tes' => 'boolean',
    ];

    public function jenisTagihan()
    {
        return $this->hasMany(JenisTagihanLembaga::class);
    }
    
    public function siswas()
    {
        return $this->hasMany(
            \App\Models\Siswa::class
        );
    }

    public function modules()
    {
        return $this->hasMany(LembagaModule::class);
    }

    public function activeModules()
    {
        return $this->modules()->aktif()->with('modulePrice');
    }

    /**
     * Jumlah siswa AKTIF saat ini (live, bukan snapshot) — dipakai job
     * billing bulanan untuk mengisi jumlah_siswa_billing. Kode lain
     * yang butuh angka billing SEHARUSNYA baca kolom
     * jumlah_siswa_billing (snapshot terkunci), bukan panggil method
     * ini langsung, supaya tagihan tidak berubah-ubah di tengah bulan.
     */
    public function jumlahSiswaAktif(): int
    {
        return $this->siswas()->where('status_siswa', 'Aktif')->count();
    }

    /**
     * Urutan Lembaga ke berapa dalam Yayasannya, untuk keperluan
     * diskon volume Akses Platform. Pakai urutan_billing kalau sudah
     * diisi manual, fallback ke urutan pendaftaran (id ascending)
     * supaya selalu deterministik walau belum pernah di-set.
     */
    public function urutanBillingKe(): int
    {
        if ($this->urutan_billing !== null) {
            return (int) $this->urutan_billing;
        }

        return Lembaga::withoutGlobalScopes()
            ->where('yayasan_id', $this->yayasan_id)
            ->where('id', '<=', $this->id)
            ->count();
    }
}
