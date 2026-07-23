<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'yayasan_id',
        'subscription_plan_id',
        'status',
        'mulai_pada',
        'berakhir_pada',
    ];

    protected function casts(): array
    {
        return [
            'mulai_pada' => 'datetime',
            'berakhir_pada' => 'datetime',
        ];
    }

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->berakhir_pada
            && $this->berakhir_pada->isFuture();
    }
}
