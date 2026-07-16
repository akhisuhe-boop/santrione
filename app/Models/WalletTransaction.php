<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use BelongsToTenant;

    // Scoping lewat wallet.siswa.lembaga
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('wallet.siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

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
