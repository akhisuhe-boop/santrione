<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['key', 'nama', 'template', 'keterangan_placeholder'];

    /**
     * Ambil template dari database (key), ganti semua {placeholder}
     * dengan nilai dari $data, dan kembalikan hasil string jadi.
     * Kalau template dengan key itu tidak ditemukan di database,
     * fallback ke $default (STRING SUDAH JADI, bukan template lagi)
     * -- supaya kalau baris seeder belum sempat jalan/terhapus tidak
     * sengaja, notifikasi tetap terkirim (pakai isi lama yang
     * hardcode), bukan gagal total.
     */
    public static function render(string $key, array $data, string $default = ''): string
    {
        $template = static::where('key', $key)->first();

        $isi = $template?->template ?? $default;

        foreach ($data as $placeholder => $value) {
            $isi = str_replace('{' . $placeholder . '}', (string) $value, $isi);
        }

        return $isi;
    }
}
