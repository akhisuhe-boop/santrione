<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'xsender' => [
    'api_key' => env('XSENDER_API_KEY'),
    'sender' => env('XSENDER_SENDER'),
    'endpoint' => env('XSENDER_ENDPOINT', 'https://xsender.id/id/send-message'),
    ],

    'doku' => [
    'client_id'      => env('DOKU_CLIENT_ID'),
    'secret_key'     => env('DOKU_SECRET_KEY'),
    'is_production'  => env('DOKU_IS_PRODUCTION', false),
    'fee_persen'     => env('DOKU_FEE_PERSEN', 0.75), // persentase fee admin Qinara dari nominal tagihan
    'fee_cap'        => env('DOKU_FEE_CAP', 10000), // batas maksimum fee admin per transaksi (Rupiah)
    // Private key RSA untuk endpoint SNAP (QRIS Direct API) -- lihat
    // catatan setup lengkap di DokuService::getAccessToken(). Isi di
    // .env sebagai DOKU_PRIVATE_KEY, format PEM dengan newline literal
    // "\n" (bukan newline asli), contoh:
    // DOKU_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nMII...\n-----END PRIVATE KEY-----"
    'private_key'    => str_replace('\n', "\n", (string) env('DOKU_PRIVATE_KEY', '')),
    ],

    'xendit' => [
    'secret_key'     => env('XENDIT_SECRET_KEY'),
    'webhook_token'  => env('XENDIT_WEBHOOK_TOKEN'), // dari Log Webhook di dashboard Xendit, untuk verifikasi callback
    'fee_persen'     => env('XENDIT_FEE_PERSEN', 1.0), // persentase fee Qinara dari tiap transaksi wali
    'main_account_id' => env('XENDIT_MAIN_ACCOUNT_ID'), // ID akun utama Qinara (bukan sub-account Lembaga) untuk terima porsi fee
    ],

    // Kredensial WA MILIK QINARA SENDIRI (Xsender, provider yang sama
    // dipakai sekolah lewat WhatsappSetting per-Lembaga) -- KHUSUS
    // untuk notifikasi platform ke tenant (tagihan langganan,
    // broadcast, reminder trial). SENGAJA terpisah dari
    // WhatsappSetting per-Lembaga -- notifikasi platform TIDAK BOLEH
    // meminjam nomor WA sekolah manapun.
    'qinara_whatsapp' => [
    'api_url' => env('QINARA_WHATSAPP_API_URL'),
    'token'   => env('QINARA_WHATSAPP_TOKEN'),
    'sender'  => env('QINARA_WHATSAPP_SENDER'),
    ],

];
