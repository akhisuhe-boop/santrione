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
        $builder->whereHas('lembaga', fn ($q) => $q->where('yayasan_id', $yayasanId));
    }

    protected $fillable = [
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

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function habisStok(): bool
    {
        return $this->stok !== null && $this->stok <= 0;
    }
}
