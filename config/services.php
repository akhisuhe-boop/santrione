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
    // partnerServiceId per bank untuk VA SNAP -- WAJIB diminta ke tim
    // DOKU (bukan bisa dikarang sendiri), lihat catatan lengkap di
    // DokuService::buatVaSnap(). Isi setelah dapat dari DOKU, format:
    // 'BCA' => env('DOKU_VA_SNAP_BCA', ''),
    // Public key milik DOKU (dari Settings > API Keys > "DOKU Public
    // Key") -- dipakai untuk VERIFIKASI signature saat DOKU memanggil
    // "Token URL" kita (arah kebalikan dari getAccessToken() -- lihat
    // DokuWebhookController::tokenB2B()). Isi di .env sebagai
    // DOKU_PUBLIC_KEY, format sama seperti DOKU_PRIVATE_KEY (newline
    // literal \n).
    'doku_public_key' => str_replace('\n', "\n", (string) env('DOKU_PUBLIC_KEY', '')),
    // accountNo (BUKAN profileId) milik Qinara sendiri di DOKU Sub
    // Account -- WAJIB ada supaya Split Rule (lihat DokuService::
    // buatSplitRule()) punya tujuan valid untuk porsi fee Qinara.
    // Dikonfirmasi dari OpenAPI spec resmi Split Rule Items: "accountNumber
    // ... Must be an existing sub-account under your merchant" -- jadi
    // Qinara juga WAJIB register diri sendiri sebagai 1 sub-account
    // (lewat DokuService::registerSubAccount() dengan data Qinara
    // sendiri, BUKAN data Lembaga) sebelum split rule bisa dibuat, lalu
    // isi accountNo hasilnya di sini. Belum bisa dipastikan APAKAH
    // accountNo dari akun MERCHANT UTAMA (non-sub-account) juga valid
    // dipakai langsung di sini tanpa registrasi sub-account terpisah --
    // WAJIB ditanyakan ke tim onboarding DOKU.
    'platform_account_no' => env('DOKU_PLATFORM_ACCOUNT_NO', ''),
    'va_snap_partner_service_id' => [
        'BCA' => env('DOKU_VA_SNAP_BCA', ''),
        'BNI' => env('DOKU_VA_SNAP_BNI', ''),
        'BRI' => env('DOKU_VA_SNAP_BRI', ''),
        'MANDIRI' => env('DOKU_VA_SNAP_MANDIRI', ''),
        'BSI' => env('DOKU_VA_SNAP_BSI', ''),
        'BJB' => env('DOKU_VA_SNAP_BJB', ''),
    ],
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
    // merchantId & terminalId untuk QRIS SNAP (qr-mpm-generate) --
    // DITAMBAHKAN setelah dicek ulang ke developers.doku.com: kedua
    // field ini MANDATORY di request body resmi DOKU, tapi sebelumnya
    // tidak dikirim sama sekali oleh DokuService::buatQris() -- WAJIB
    // penyebab QRIS selalu gagal. Nilainya diterbitkan DOKU per akun
    // (bukan bisa dikarang), minta ke tim onboarding: "mohon merchantId
    // & terminalId untuk QRIS MPM akun sandbox kami (Client-Id: ...)".
    'qris_merchant_id' => env('DOKU_QRIS_MERCHANT_ID', ''),
    'qris_terminal_id' => env('DOKU_QRIS_TERMINAL_ID', ''),
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
