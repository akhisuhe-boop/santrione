<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappSetting;

class WhatsappService
{
    /**
     * @param  int|null  $lembagaId  WAJIB diisi untuk kirim dari job/queue
     *         (tidak ada user login di context itu, jadi tidak bisa
     *         mengandalkan tenant scope otomatis). Kalau null, fallback
     *         ke setting aktif pertama yang ditemukan (kurang akurat untuk
     *         yayasan dengan >1 lembaga, tapi tetap jalan agar tidak
     *         gagal total).
     */
    public static function send($phone, $message, $lembagaId = null)
    {
        try {

            $query = WhatsappSetting::withoutGlobalScopes()->where('is_active', 1);

            if ($lembagaId) {
                $query->where('lembaga_id', $lembagaId);
            }

            $setting = $query->first();

            if (!$setting) {
                Log::error('WhatsApp setting tidak ditemukan', ['lembaga_id' => $lembagaId]);
                return false;
            }

            // ubah format nomor ke 62
            $phone = preg_replace('/^0/', '62', $phone);

            $response = Http::asForm()->post($setting->api_url, [
                'api_key' => $setting->token,
                'sender'  => $setting->sender,
                'number'  => $phone,
                'message' => $message,
            ]);

            Log::info('XSENDER RESPONSE', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);

            return $response->successful();

        } catch (\Exception $e) {

            Log::error('XSender Error: ' . $e->getMessage());
            return false;

        }
    }

    /**
     * Kirim WA pakai kredensial QINARA SENDIRI (config/services.php
     * -> qinara_whatsapp), TIDAK meminjam WhatsappSetting Lembaga
     * manapun -- KHUSUS notifikasi level platform (tagihan
     * langganan, broadcast, reminder trial). Struktur request HTTP
     * SAMA PERSIS dengan send() di atas (provider Xsender yang sama),
     * cuma sumber kredensialnya beda.
     */
    public static function sendPlatform($phone, $message)
    {
        try {
            $setting = \App\Models\PlatformWhatsappSetting::current();

            $apiUrl = $setting->api_url ?: config('services.qinara_whatsapp.api_url');
            $token = $setting->token ?: config('services.qinara_whatsapp.token');
            $sender = $setting->sender ?: config('services.qinara_whatsapp.sender');

            if (! $apiUrl || ! $token || ! $sender) {
                Log::error('WhatsappService::sendPlatform: kredensial QINARA_WHATSAPP_* belum lengkap di .env');

                return false;
            }

            $phone = preg_replace('/^0/', '62', $phone);

            $response = Http::asForm()->post($apiUrl, [
                'api_key' => $token,
                'sender'  => $sender,
                'number'  => $phone,
                'message' => $message,
            ]);

            Log::info('XSENDER RESPONSE (platform)', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $response->successful();

        } catch (\Exception $e) {

            Log::error('XSender Error (platform): ' . $e->getMessage());

            return false;
        }
    }
}