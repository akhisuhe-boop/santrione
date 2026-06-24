<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappSetting;

class WhatsappService
{
    public static function send($phone, $message)
    {
        try {

            $setting = WhatsappSetting::where('is_active', 1)->first();

            if (!$setting) {
                Log::error('WhatsApp setting tidak ditemukan');
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
}