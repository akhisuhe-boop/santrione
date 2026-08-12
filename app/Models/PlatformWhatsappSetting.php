<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformWhatsappSetting extends Model
{
    protected $fillable = ['api_url', 'token', 'sender'];

    /**
     * Selalu kembalikan baris id=1 (bikin kalau belum ada) -- pola
     * singleton, panel cuma pernah edit 1 baris ini, tidak ada
     * konsep "banyak pengaturan WA platform".
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
