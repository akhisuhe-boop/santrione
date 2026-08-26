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
    | CATATAN (diubah 7 Sep 2026): dulu angka ini dipakai untuk MENUNDA
    | restriksi akses (baru di-suspend N hari setelah jatuh tempo).
    | Sekarang TIDAK LAGI begitu -- akses langsung dibatasi (sidebar
    | cuma menu Langganan) begitu jatuh tempo lewat, lihat
    | CheckExpiredSubscriptions. Angka ini sekarang murni jadi jendela
    | pengingat WA H-5/H-3/H-1 (lihat SendGracePeriodReminders) --
    | "masih ada N hari sejak jatuh tempo sebelum kami anggap benar-benar
    | churn", walau secara akses sudah dibatasi dari hari pertama.
    |
    */

    'grace_period_days' => env('SUBSCRIPTION_GRACE_PERIOD_DAYS', 7),

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
