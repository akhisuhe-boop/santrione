<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'subscription_id',
        'jumlah',
        'metode',
        'status',
        'bukti_transfer',
        'diverifikasi_oleh',
        'diverifikasi_pada',
        'gateway_order_id',
        'gateway_transaction_id',
        'gateway_raw_response',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'diverifikasi_pada' => 'datetime',
            'gateway_raw_response' => 'array',
        ];
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }
}
