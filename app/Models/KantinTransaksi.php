<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class KantinTransaksi extends Model
{
    use BelongsToTenant;

    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected static function booted(): void
    {
        static::creating(function (self $trx) {
            if (empty($trx->kode)) {
                $trx->kode = 'KTN-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });
    }

    protected $fillable = [
        'lembaga_id',
        'kode',
        'siswa_id',
        'wallet_id',
        'metode',
        'total',
        'kasir_id',
        'kas_id',
        'tanggal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime',
        ];
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function kasir()
    {
        return $this->belongsTo(Pegawai::class, 'kasir_id');
    }

    public function items()
    {
        return $this->hasMany(KantinTransaksiItem::class);
    }
}
