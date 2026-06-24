<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'siswa_id',
        'saldo',
    ];

    protected $casts = [
        'saldo' => 'decimal:2',
    ];

    // 🔹 Relasi ke siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }
    // 🔹 Relasi ke transaksi
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // 🔹 Relasi ke withdraw
    public function withdrawRequests()
    {
        return $this->hasMany(WithdrawRequest::class);
    }
}
