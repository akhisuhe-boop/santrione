<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $fillable = [
        'brand_name', 'logo', 'headline_baris1', 'headline_baris2', 'subheadline', 'badge_text',
        'whatsapp_number', 'whatsapp_pesan_default', 'email_kontak', 'alamat',
        'ig_url', 'fb_url', 'yt_url', 'x_url',
        'hero_mockup_gambar', 'hero_kpi_keuangan', 'hero_kpi_keuangan_growth', 'hero_kpi_kehadiran_persen',
        'social_proof_text', 'stat_efisiensi', 'stat_modul', 'stat_akses', 'stat_digitalisasi',
        'footer_text', 'footer_legalitas', 'nomor_nib', 'nomor_akta',
    ];

    /**
     * LandingSetting bersifat singleton (selalu 1 baris, id = 1).
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['brand_name' => 'Qinara Apps']);
    }
}
