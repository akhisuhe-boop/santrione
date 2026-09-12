<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DITAMBAHKAN -- kategori pemasukan/pengeluaran untuk pembukuan
 * internal Qinara sendiri (bukan kas sekolah/Yayasan client).
 */
class QinaraKasKategori extends Model
{
    protected $table = 'qinara_kas_kategoris';

    protected $fillable = [
        'nama',
        'tipe',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function transaksis()
    {
        return $this->hasMany(QinaraKasTransaksi::class, 'kategori_id');
    }
}
