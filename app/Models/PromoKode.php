<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoKode extends Model
{
    protected $fillable = [
        'kode',
        'deskripsi',
        'diskon_persen',
        'maks_pemakaian',
        'berlaku_sampai',
        'aktif',
    ];

    protected $casts = [
        'berlaku_sampai' => 'date',
        'aktif' => 'boolean',
    ];

    public function pemakaians()
    {
        return $this->hasMany(PromoKodePemakaian::class);
    }

    /**
     * Cek apakah kode ini SAH dipakai oleh $yayasan sekarang. Return
     * null kalau valid, atau STRING alasan kalau tidak valid (dipakai
     * langsung sebagai pesan error ke tenant).
     */
    public function alasanTidakValidUntuk(Yayasan $yayasan): ?string
    {
        if (! $this->aktif) {
            return 'Kode promo ini sudah tidak aktif.';
        }

        if ($this->berlaku_sampai && $this->berlaku_sampai->isPast()) {
            return 'Kode promo ini sudah kedaluwarsa.';
        }

        if ($this->maks_pemakaian !== null) {
            $sudahDipakai = $this->pemakaians()->where('yayasan_id', $yayasan->id)->count();

            if ($sudahDipakai >= $this->maks_pemakaian) {
                return 'Kode promo ini sudah mencapai batas pemakaian untuk Yayasan Anda.';
            }
        }

        return null;
    }
}
