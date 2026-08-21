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
    'fee_persen'     => env('DOKU_FEE_PERSEN', 0.75), // persentase fee admin QINARA dari nominal tagihan
    'fee_cap'        => env('DOKU_FEE_CAP', 10000), // batas maksimum fee admin Qinara per transaksi (Rupiah)
    // Estimasi fee DOKU sendiri per channel -- DOKU MEMOTONG fee ini dari
    // settlement (bukan nambah otomatis ke customer, dikonfirmasi resmi
    // oleh tim DOKU). Supaya Qinara tidak "makan" fee ini dari margin
    // sendiri, nilainya DITAMBAHKAN juga ke nominal yang di-charge ke
    // wali murid (lihat DokuService::hitungFeeTotal()). Angka berikut
    // berdasar price list resmi DOKU yang sudah dibandingkan sebelumnya
    // -- SEBAIKNYA dikonfirmasi ulang ke akun sandbox/production Anda
    // karena bisa beda per kontrak/tier volume.
    'fee_doku' => [
        // Angka RESMI dari halaman harga DOKU (doku.com/harga), bukan
        // estimasi lagi. VA: BCA khusus Rp4.500, bank lain Rp4.000 --
        // TAPI implementasi VA kita saat ini pakai endpoint VA UNIVERSAL
        // (bukan benar-benar per-bank di sisi DOKU, lihat catatan di
        // DokuService::buatVaLangsung()), jadi saya pakai angka
        // TERTINGGI (Rp4.500) untuk semua VA supaya Qinara tidak
        // kekurangan margin kalau ternyata kena tarif BCA.
        // OVO & ShopeePay resmi berupa RENTANG (OVO 2%-3,18%, ShopeePay
        // 2%-4%) -- dipakai batas ATAS supaya aman, TIDAK rugi.
        'VA'        => ['flat' => 4500, 'persen' => 0],
        'QRIS'      => ['flat' => 0,    'persen' => 0.7],
        'DANA'      => ['flat' => 0,    'persen' => 1.5],
        'SHOPEEPAY' => ['flat' => 0,    'persen' => 4.0],
        'OVO'       => ['flat' => 0,    'persen' => 3.18],
        'ALFAMART'  => ['flat' => 5000, 'persen' => 0],
        'INDOMARET' => ['flat' => 6500, 'persen' => 0],
    ],
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
