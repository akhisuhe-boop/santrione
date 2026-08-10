<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModulePrice extends Model
{
    protected $fillable = [
        'key',
        'nama',
        'harga_bulanan',
        'dibebankan_ke',
        'is_gratis',
        'is_active',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'is_gratis' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function lembagaModules()
    {
        return $this->hasMany(LembagaModule::class);
    }

    /**
     * Harga yang benar-benar ditagih ke sekolah — modul gratis (mis.
     * Keuangan, e-Kantin) selalu Rp0 di sisi sekolah walau kolom
     * harga_bulanan diisi (dipakai sebagai referensi "kalau bayar
     * satu-satu" di ilustrasi/dokumen penawaran, bukan angka tagih).
     */
    public function hargaTagihSekolah(): int
    {
        return $this->is_gratis ? 0 : (int) $this->harga_bulanan;
    }
}
