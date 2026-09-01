<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use BelongsToTenant;

    // Wallet dimiliki SISWA atau PEGAWAI (guru/staf) -- tidak pernah dua-
    // duanya. Scoping tenant harus cek dua jalur relasi sekaligus, karena
    // siswa.lembaga dan pegawai.yayasan_id adalah dua jalur yang berbeda.
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->where(function ($q) use ($yayasanId) {
            $q->whereHas('siswa.lembaga', fn ($q2) => $q2->where('yayasan_id', $yayasanId))
                ->orWhereHas('pegawai', fn ($q2) => $q2->where('yayasan_id', $yayasanId));
        });
    }

    protected $fillable = [
        'siswa_id',
        'pegawai_id',
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

    // 🔹 Relasi ke pegawai (guru/staf)
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
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
