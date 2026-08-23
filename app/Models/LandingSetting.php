<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $fillable = [
        'brand_name', 'logo', 'headline_baris1', 'headline_baris2', 'subheadline', 'badge_text',
        'whatsapp_number', 'whatsapp_pesan_default', 'email_kontak', 'alamat',
        'ig_url', 'fb_url', 'yt_url', 'x_url',
        'hero_mockup_gambar', 'hero_images', 'hero_video_url', 'video_deskripsi', 'hero_kpi_keuangan', 'hero_kpi_keuangan_growth', 'hero_kpi_kehadiran_persen',
        'social_proof_text', 'stat_efisiensi', 'stat_modul', 'stat_akses', 'stat_digitalisasi',
        'footer_text', 'footer_legalitas', 'nomor_nib', 'nomor_akta',
        'meta_pixel_id', 'tiktok_pixel_id', 'google_ads_id',
        'crm_notif_wa_numbers',
        'promo_aktif', 'promo_mode', 'promo_teks', 'promo_persen', 'promo_berakhir_pada', 'promo_evergreen_durasi_jam', 'tahunan_diskon_persen',
        'promo_hanya_countdown',
    ];

    protected $casts = [
        'promo_aktif' => 'boolean',
        'promo_berakhir_pada' => 'datetime',
        'promo_hanya_countdown' => 'boolean',
        'hero_images' => 'array',
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
     * Promo/countdown dianggap berjalan kalau togglenya nyala, DAN:
     *  - mode 'manual' -> tanggal berakhirnya masih di masa depan
     *    (1 tanggal tetap, sama untuk semua pengunjung).
     *  - mode 'evergreen' -> SELALU dianggap berjalan selama togglenya
     *    nyala -- tidak ada tanggal tetap di database sama sekali,
     *    karena tiap pengunjung dapat jendela waktunya sendiri-sendiri
     *    (dihitung di browser masing-masing lewat localStorage, mulai
     *    dari kunjungan pertama mereka, lihat blok JS di landing page).
     * SENGAJA tidak mewajibkan persen diskon terisi, supaya banner
     * bisa dipakai sebagai "countdown murni" (tanpa diskon harga sama
     * sekali, cuma teks + hitung mundur) kalau memang itu yang
     * diinginkan. Guard "Hemat 0%" yang membingungkan ditangani di
     * level tampilan (blade/JS), bukan di sini -- lihat promoAdaDiskon().
     */
    public function promoSedangBerjalan(): bool
    {
        if (! $this->promo_aktif) {
            return false;
        }

        if ($this->promo_mode === 'evergreen') {
            return true;
        }

        return $this->promo_berakhir_pada && $this->promo_berakhir_pada->isFuture();
    }

    /**
     * True cuma kalau promo berjalan, BUKAN mode "hanya countdown", DAN
     * persen diskonnya benar-benar diisi (>0) -- dipakai buat memutuskan
     * apakah baris "Hemat X%" ditampilkan/diterapkan ke harga, terpisah
     * dari apakah banner countdown-nya sendiri tampil atau tidak.
     */
    public function promoAdaDiskon(): bool
    {
        return $this->promoSedangBerjalan()
            && ! $this->promo_hanya_countdown
            && (int) ($this->promo_persen ?? 0) > 0;
    }
}
