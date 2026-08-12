<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTypeToggle extends Model
{
    protected $fillable = ['lembaga_id', 'key', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /**
     * TIDAK ADA baris untuk (lembaga_id, key) -> AKTIF (default, sama
     * seperti perilaku sekarang). ADA baris -> ikuti nilai
     * is_active-nya. Dipakai NotificationService (SETELAH konversi
     * bertahap, belum semua) sebelum kirim WA.
     */
    public static function isEnabled(int $lembagaId, string $key): bool
    {
        $row = static::where('lembaga_id', $lembagaId)->where('key', $key)->first();

        return $row?->is_active ?? true;
    }

    public static function setEnabled(int $lembagaId, string $key, bool $aktif): void
    {
        static::updateOrCreate(
            ['lembaga_id' => $lembagaId, 'key' => $key],
            ['is_active' => $aktif]
        );
    }
}
