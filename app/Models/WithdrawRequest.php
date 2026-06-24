<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    protected $fillable = [
        'wallet_id',
        'amount',
        'method',
        'rekening_tujuan',
        'nama_pemilik',
        'no_hp',
        'requested_by_type',
        'requested_by_id',
        'status',
        'processed_by',
        'processed_at',
        'catatan_admin',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // =========================
    // 🔗 RELASI
    // =========================
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    // =========================
    // 🔥 AUTO PROCESS LOGIC
    // =========================
    protected static function booted()
    {
        static::created(function ($withdraw) {

            /**
             * ✅ HANYA JALAN JIKA:
             * - status = approved (admin input)
             * - belum diproses (anti double eksekusi)
             */
            if (
            $withdraw->status === 'approved' &&
            is_null($withdraw->processed_at)
        ) {

            app(\App\Services\WalletService::class)
                ->approveWithdraw($withdraw);
            }
        });
    }
}