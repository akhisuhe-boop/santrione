<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LembagaNotificationTemplate extends Model
{
    protected $fillable = ['lembaga_id', 'key', 'template'];

    /**
     * Sama pola dengan App\Models\NotificationTemplate (level
     * platform), tapi discoped per Lembaga -- kalau Lembaga itu belum
     * pernah kustomisasi redaksi jenis notifikasi ini, fallback ke
     * $default (string hardcode yang sudah ada di kode).
     */
    public static function renderFor(int $lembagaId, string $key, array $data, string $default = ''): string
    {
        $template = static::where('lembaga_id', $lembagaId)->where('key', $key)->first();

        $isi = $template?->template ?? $default;

        foreach ($data as $placeholder => $value) {
            $isi = str_replace('{' . $placeholder . '}', (string) $value, $isi);
        }

        return $isi;
    }
}
