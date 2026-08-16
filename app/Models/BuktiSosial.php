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
     *
     * Dihitung manual lewat selisih timestamp Unix (bukan Carbon::diffInDays())
     * -- versi sebelumnya salah menghasilkan "Hari ini" untuk tanggal yang
     * sebenarnya sudah 16 hari lalu, jadi diganti ke perhitungan yang tidak
     * bergantung pada perilaku spesifik versi Carbon sama sekali.
     */
    public function waktuBergabungText(): ?string
    {
        if (! $this->tanggal_bergabung) {
            return null;
        }

        $today = now()->startOfDay()->timestamp;
        $joined = $this->tanggal_bergabung->copy()->startOfDay()->timestamp;
        $days = (int) floor(($today - $joined) / 86400);

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
            return intdiv($days, 7).' minggu yang lalu';
        }
        if ($days < 365) {
            return intdiv($days, 30).' bulan yang lalu';
        }

        return intdiv($days, 365).' tahun yang lalu';
    }
}
