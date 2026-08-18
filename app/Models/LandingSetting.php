<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $fillable = [
        'brand_name', 'logo', 'headline_baris1', 'headline_baris2', 'subheadline', 'badge_text',
        'whatsapp_number', 'whatsapp_pesan_default', 'email_kontak', 'alamat',
        'ig_url', 'fb_url', 'yt_url', 'x_url',
        'hero_mockup_gambar', 'hero_video_url', 'video_deskripsi', 'hero_kpi_keuangan', 'hero_kpi_keuangan_growth', 'hero_kpi_kehadiran_persen',
        'social_proof_text', 'stat_efisiensi', 'stat_modul', 'stat_akses', 'stat_digitalisasi',
        'footer_text', 'footer_legalitas', 'nomor_nib', 'nomor_akta',
        'meta_pixel_id', 'tiktok_pixel_id', 'google_ads_id',
        'crm_notif_wa_numbers',
        'promo_aktif', 'promo_teks', 'promo_persen', 'promo_berakhir_pada', 'tahunan_diskon_persen',
    ];

    protected $casts = [
        'promo_aktif' => 'boolean',
        'promo_berakhir_pada' => 'datetime',
    ];

    /**
     * LandingSetting bersifat singleton (selalu 1 baris, id = 1).
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['brand_name' => 'Qinara Apps']);
    }

    public function heroVideoIsEmbed(): bool
    {
        return $this->hero_video_url
            && (str_contains($this->hero_video_url, 'youtube.com') || str_contains($this->hero_video_url, 'youtu.be'));
    }

    public function heroVideoEmbedUrl(): ?string
    {
        if (! $this->hero_video_url) {
            return null;
        }

        if (! $this->heroVideoIsEmbed()) {
            return $this->hero_video_url;
        }

        $separator = str_contains($this->hero_video_url, '?') ? '&' : '?';

        return $this->hero_video_url.$separator.'autoplay=1&mute=1&rel=0&modestbranding=1&playsinline=1';
    }

    /**
     * Promo dianggap SUNGGUH-SUNGGUH berjalan kalau: togglenya nyala,
     * tanggal berakhirnya masih di masa depan, DAN persen diskonnya
     * sudah diisi >0. Sengaja cek persen di sini juga -- kalau admin
     * baru nyalakan toggle tapi belum sempat isi angka persennya,
     * banner "Hemat 0%" yang membingungkan tidak akan ikut muncul.
     */
    public function promoSedangBerjalan(): bool
    {
        return $this->promo_aktif
            && $this->promo_berakhir_pada
            && $this->promo_berakhir_pada->isFuture()
            && (int) ($this->promo_persen ?? 0) > 0;
    }
}
