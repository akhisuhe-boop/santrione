<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'harga_bulanan',
        'maks_lembaga',
        'maks_siswa',
        'fitur',
        'is_active',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'fitur' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $plan) {
            if (empty($plan->slug) && ! empty($plan->nama)) {
                $plan->slug = \Illuminate\Support\Str::slug($plan->nama);
            }
        });
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasFeature(string $key): bool
    {
        return in_array($key, $this->fitur ?? [], true);
    }
}
