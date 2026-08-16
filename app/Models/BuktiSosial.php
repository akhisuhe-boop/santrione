<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BuktiSosial extends Model
{
    protected $table = 'bukti_sosials';

    protected $fillable = ['nama_lembaga', 'lokasi', 'tanggal_bergabung', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_bergabung' => 'date',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('urutan');
    }

    /**
     * Teks waktu relatif Bahasa Indonesia, dihitung dari tanggal_bergabung.
     * Ditulis manual (bukan pakai diffForHumans() bawaan Carbon) supaya
     * hasilnya selalu Indonesia, tidak tergantung locale Carbon di app.
     */
    public function waktuBergabungText(): ?string
    {
        if (! $this->tanggal_bergabung) {
            return null;
        }

        $days = (int) now()->startOfDay()->diffInDays($this->tanggal_bergabung->copy()->startOfDay());

        if ($days <= 0) {
            return 'Hari ini';
        }
        if ($days === 1) {
            return 'Kemarin';
        }
        if ($days < 7) {
            return $days.' hari yang lalu';
        }
        if ($days < 30) {
            $weeks = intdiv($days, 7);

            return $weeks.' minggu yang lalu';
        }
        if ($days < 365) {
            $months = intdiv($days, 30);

            return $months.' bulan yang lalu';
        }

        $years = intdiv($days, 365);

        return $years.' tahun yang lalu';
    }
}
