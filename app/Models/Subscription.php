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
        'computed_amount',
        'computed_breakdown',
        'periode',
    ];

    protected function casts(): array
    {
        return [
            'mulai_pada' => 'datetime',
            'berakhir_pada' => 'datetime',
            'computed_breakdown' => 'array',
        ];
    }

    /**
     * Nominal yang benar-benar ditagih — kalau sudah pernah dihitung
     * lewat TenantBillingCalculator (skema à la carte baru) pakai itu,
     * kalau belum (subscription lama / plan flat all-or-nothing)
     * fallback ke harga_bulanan plan seperti sebelumnya. SATU-SATUNYA
     * tempat nominal tagihan ditentukan — DuitkuSubscriptionService
     * dan tampilan invoice sama-sama wajib panggil method ini, supaya
     * angka yang ditagih dan angka yang ditampilkan tidak pernah beda.
     */
    public function totalTagihan(): int
    {
        return (int) ($this->computed_amount ?? $this->plan?->harga_bulanan ?? 0);
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
