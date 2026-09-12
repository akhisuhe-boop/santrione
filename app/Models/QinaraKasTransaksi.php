<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DITAMBAHKAN -- transaksi kas masuk/keluar untuk pembukuan internal
 * bisnis Qinara sendiri (revenue langganan, biaya operasional, gaji,
 * dll). Terpisah total dari model Kas (itu untuk sekolah/Yayasan
 * client, per-tenant).
 */
class QinaraKasTransaksi extends Model
{
    protected $table = 'qinara_kas_transaksis';

    protected $fillable = [
        'tanggal',
        'tipe',
        'kategori_id',
        'nominal',
        'keterangan',
        'diinput_oleh_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function kategori()
    {
        return $this->belongsTo(QinaraKasKategori::class, 'kategori_id');
    }

    public function diinputOleh()
    {
        return $this->belongsTo(User::class, 'diinput_oleh_id');
    }
}
