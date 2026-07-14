<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToTenant;

class Yayasan extends Model
{
    use BelongsToTenant;

    // Yayasan adalah tenant itu sendiri — filter langsung berdasarkan id-nya
    // sendiri (bukan lewat relasi lembaga), harus sama dengan yayasan_id
    // milik user yang login.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where('id', $yayasanId);
    }

    protected $fillable = [
    'nama',
    'ketua',
    'logo',
    'alamat',
    'telepon',
    'email',
    'website',
    ];

    public function lembagas()
    {
        return $this->hasMany(Lembaga::class);
    }
}