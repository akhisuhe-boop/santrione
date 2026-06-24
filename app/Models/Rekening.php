<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    protected $fillable = [
        'nama',
        'bank',
        'no_rekening',
        'atas_nama',
        'tipe',
        'is_active',
    ];

    // =========================
    // RELASI KE KAS
    // =========================
    public function kas()
    {
        return $this->hasMany(\App\Models\Kas::class);
    }

    // =========================
    // HITUNG SALDO (OTOMATIS)
    // =========================
    public function getSaldoAttribute()
    {
        return $this->kas()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN tipe = 'masuk' THEN nominal ELSE 0 END),0) -
                COALESCE(SUM(CASE WHEN tipe = 'keluar' THEN nominal ELSE 0 END),0)
                as saldo
            ")
            ->value('saldo') ?? 0;
    }

    // =========================
    // LABEL UNTUK DROPDOWN (OPSIONAL)
    // =========================
    public function getLabelAttribute()
    {
        return "{$this->nama} - {$this->bank} ({$this->no_rekening})";
    }
}