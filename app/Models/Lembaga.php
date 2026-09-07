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
    'doku_split_rule_id_flat',
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

    public function rekenings()
    {
        return $this->hasMany(\App\Models\LembagaRekening::class);
    }

    /**
     * DIUBAH -- sebelumnya menerima JenisTagihan.tipe_sistem lalu
     * diterjemahkan lewat kategoriDariTipeSistem() ke kategori rekening.
     * DIPISAH TOTAL sekarang: parameter ini adalah JenisTagihan.
     * kategori_rekening LANGSUNG -- field baru yang independen dari
     * tipe_sistem (yang tetap dipakai murni untuk logika alur PPDB
     * otomatis, tidak disentuh sama sekali oleh perubahan ini). Alasan
     * pemisahan: tipe_sistem cuma py 2 nilai tetap (PPDB), kalau dipaksa
     * menampung kategori rekening bebas (uang gedung, seragam, dst)
     * berisiko mengacaukan kode lain yang query spesifik nilai
     * tipe_sistem itu.
     *
     * Urutan fallback:
     * 1. LembagaRekening dengan kategori yang persis cocok.
     * 2. LembagaRekening kategori 'default' (kalau Lembaga sengaja
     *    setup 1 rekening umum tapi belum pisah semua kategori).
     * 3. Kolom doku_* LANGSUNG di tabel `lembagas` (rekening lama/
     *    legacy -- SEMUA Lembaga yang sudah didaftarkan sebelum fitur
     *    multi-rekening ini ada tetap jalan tanpa migrasi data apapun).
     *
     * $kategoriRekening: nilai bebas dari JenisTagihan::kategori_rekening
     * (mis. 'ppdb', 'uang_gedung') -- null/kosong -> langsung ke
     * kategori 'default'. TIDAK ada daftar tetap, TIDAK perlu ubah kode
     * ini setiap kali ada kategori baru -- cukup isi field itu di Jenis
     * Tagihan & daftarkan rekening dengan kategori yang sama persis.
     *
     * Return array ternormalisasi (BUKAN model) supaya konsumen
     * (DokuService::pilihSplitRuleId(), controller) tidak perlu tahu
     * apakah sumbernya dari LembagaRekening atau kolom legacy Lembaga.
     */
    public function rekeningUntuk(?string $kategoriRekening): array
    {
        $kategori = $kategoriRekening ?: 'default';

        $rekening = $this->rekenings->firstWhere('kategori', $kategori)
            ?? $this->rekenings->firstWhere('kategori', 'default');

        if ($rekening) {
            return [
                'sub_account_id' => $rekening->doku_sub_account_id,
                'account_no' => $rekening->doku_account_no,
                'split_rule_id' => $rekening->doku_split_rule_id,
                'split_rule_id_flat' => $rekening->doku_split_rule_id_flat,
            ];
        }

        return [
            'sub_account_id' => $this->doku_sub_account_id,
            'account_no' => $this->doku_account_no,
            'split_rule_id' => $this->doku_split_rule_id,
            'split_rule_id_flat' => $this->doku_split_rule_id_flat,
        ];
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
