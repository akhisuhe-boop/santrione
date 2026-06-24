<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'status',
        'reference_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // 🔹 Relasi ke wallet
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
