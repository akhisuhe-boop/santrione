<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Masa Trial
    |--------------------------------------------------------------------------
    |
    | Berapa hari yayasan baru boleh pakai aplikasi gratis sebelum wajib
    | berlangganan. Ubah di sini kalau mau ganti kebijakan (mis. promo
    | 30 hari), tidak perlu ubah kode.
    |
    */

    'trial_days' => env('SUBSCRIPTION_TRIAL_DAYS', 14),

    // Yayasan trial yang habis TANPA pernah bayar sama sekali dihapus
    // permanen setelah sekian hari lewat trial_ends_at (lihat
    // command subscription:purge-expired-trials).
    'purge_trial_after_days' => env('SUBSCRIPTION_PURGE_TRIAL_AFTER_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | Masa Tenggang Setelah Langganan Berakhir
    |--------------------------------------------------------------------------
    |
    | Kalau langganan berbayar sudah lewat tanggal berakhir tapi belum
    | diperpanjang, beri toleransi sekian hari sebelum akun benar-benar
    | di-suspend (jaga-jaga keterlambatan transfer/konfirmasi).
    |
    */

    'grace_period_days' => env('SUBSCRIPTION_GRACE_PERIOD_DAYS', 3),

    /*
    |--------------------------------------------------------------------------
    | Midtrans
    |--------------------------------------------------------------------------
    |
    | Isi di .env setelah punya akun Midtrans (sandbox/production).
    | Selama kosong, tombol bayar otomatis akan disembunyikan dan hanya
    | jalur transfer manual yang aktif.
    |
    */

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rekening Transfer Manual
    |--------------------------------------------------------------------------
    |
    | Ditampilkan di halaman pembayaran manual supaya customer tahu mau
    | transfer ke mana.
    |
    */

    'manual_transfer' => [
        'bank' => env('SUBSCRIPTION_BANK_NAME', ''),
        'nomor_rekening' => env('SUBSCRIPTION_BANK_ACCOUNT_NUMBER', ''),
        'atas_nama' => env('SUBSCRIPTION_BANK_ACCOUNT_NAME', ''),
    ],

];
