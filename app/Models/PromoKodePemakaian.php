<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoKodePemakaian extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'promo_kode_id',
        'yayasan_id',
        'subscription_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->created_at ??= now();
        });
    }

    public function promoKode()
    {
        return $this->belongsTo(PromoKode::class);
    }

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
