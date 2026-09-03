<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Kantin extends Model
{
    use BelongsToTenant;

    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('yayasan_id', $yayasanId);
    }

    protected $fillable = [
        'yayasan_id',
        'lembaga_id',
        'nama',
        'is_active',
        'limit_tunai_kantin_harian',
        'pin',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $kantin) {
            if (empty($kantin->yayasan_id)) {
                $kantin->yayasan_id = \Filament\Facades\Filament::getTenant()?->id
                    ?? auth()->user()?->yayasan_id;
            }
        });

        // PIN disimpan hash, bukan plain text -- di-hash otomatis tiap
        // kali diisi/diubah (baik saat create maupun update).
        static::saving(function (self $kantin) {
            if ($kantin->isDirty('pin') && filled($kantin->pin) && ! \Illuminate\Support\Facades\Hash::isHashed($kantin->pin)) {
                $kantin->pin = \Illuminate\Support\Facades\Hash::make($kantin->pin);
            }
        });
    }

    public function cekPin(string $pin): bool
    {
        if (blank($this->pin)) {
            return true; // kantin tanpa PIN -- bebas dipilih siapa saja
        }

        return \Illuminate\Support\Facades\Hash::check($pin, $this->pin);
    }

    // Tag opsional -- bukan scoping wajib. Kosong berarti kantin ini
    // tidak dinisbatkan ke lembaga manapun (mis. kantin bersama).
    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class);
    }

    public function produk()
    {
        return $this->hasMany(KantinProduk::class);
    }

    public function transaksi()
    {
        return $this->hasMany(KantinTransaksi::class);
    }
}
