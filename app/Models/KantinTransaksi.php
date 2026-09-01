<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class KantinTransaksi extends Model
{
    use BelongsToTenant;

    /**
     * Scoping tenant LANGSUNG lewat kolom yayasan_id (bukan lagi
     * whereHas('lembaga', ...)) -- supaya transaksi pengunjung umum
     * (lembaga_id kosong, tidak diatribusikan ke lembaga manapun) tetap
     * kelihatan oleh yayasan yang punya kasirnya. Pola yang sama dengan
     * Kas::applyTenantScope().
     */
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected static function booted(): void
    {
        static::creating(function (self $trx) {
            if (empty($trx->kode)) {
                $trx->kode = 'KTN-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }

            if (empty($trx->yayasan_id)) {
                $trx->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }
        });
    }

    protected $fillable = [
        'lembaga_id',
        'yayasan_id',
        'kode',
        'siswa_id',
        'pegawai_id',
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

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
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
