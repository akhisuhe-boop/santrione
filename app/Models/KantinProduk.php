<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class KantinProduk extends Model
{
    use BelongsToTenant;

    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('kantin', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [
        'kantin_id',
        'lembaga_id',
        'nama',
        'barcode',
        'kategori',
        'harga',
        'stok',
        'gambar',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function kantin()
    {
        return $this->belongsTo(Kantin::class);
    }

    // Dipertahankan untuk kompatibilitas data lama -- tidak lagi wajib
    // diisi, dan tidak lagi dipakai untuk scoping tenant (lihat kantin()).
    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function habisStok(): bool
    {
        return $this->stok !== null && $this->stok <= 0;
    }
}
