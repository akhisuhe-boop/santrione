<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use BelongsToTenant;

    // Scoping lewat siswa.lembaga (tidak ada lembaga_id langsung)
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('siswa.lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

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
