<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KantinTransaksiItem extends Model
{
    protected $fillable = [
        'kantin_transaksi_id',
        'kantin_produk_id',
        'nama_produk',
        'harga_satuan',
        'qty',
        'subtotal',
    ];

    public function transaksi()
    {
        return $this->belongsTo(KantinTransaksi::class, 'kantin_transaksi_id');
    }

    public function produk()
    {
        return $this->belongsTo(KantinProduk::class, 'kantin_produk_id');
    }
}
