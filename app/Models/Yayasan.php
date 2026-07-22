<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\BelongsToTenant;
use Filament\Models\Contracts\HasName;

class Yayasan extends Model implements HasName
{
    use BelongsToTenant;

    // Filament butuh tahu kolom mana yang jadi "nama tampilan" tenant —
    // tabel kita pakai 'nama', bukan 'name' bawaan default Filament.
    public function getFilamentName(): string
    {
        return $this->nama ?? 'Yayasan';
    }

    // Yayasan adalah tenant itu sendiri — filter langsung berdasarkan id-nya
    // sendiri (bukan lewat relasi lembaga), harus sama dengan yayasan_id
    // milik user yang login.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('id', $yayasanId);
    }

    protected static function booted(): void
    {
        static::creating(function (self $yayasan) {
            if (empty($yayasan->slug) && ! empty($yayasan->nama)) {
                $base = Str::slug($yayasan->nama);
                $slug = $base;
                $i = 1;

                while (
                    static::withoutGlobalScopes()->where('slug', $slug)->exists()
                ) {
                    $slug = $base . '-' . $i;
                    $i++;
                }

                $yayasan->slug = $slug;
            }
        });
    }

    protected $fillable = [
    'nama',
    'slug',
    'ketua',
    'logo',
    'alamat',
    'telepon',
    'email',
    'website',
    'tarif_pengganti_per_jp',
    ];

    public function lembagas()
    {
        return $this->hasMany(Lembaga::class);
    }
}