<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Domain Panel Platform
    |--------------------------------------------------------------------------
    |
    | Subdomain KHUSUS untuk Panel Platform (dashboard lintas-Yayasan,
    | harga modul, verifikasi pembayaran). WAJIB diisi berbeda dari
    | domain panel Yayasan (APP_URL) di .env produksi/dev.
    |
    | Default 'platform.localhost' SENGAJA dipakai kalau env belum
    | diisi — supaya panel ini tidak pernah "menimpa" domain apapun
    | secara tidak sengaja sebelum subdomain-nya benar-benar
    | dikonfigurasi & di-deploy (DNS + nginx + SSL).
    |
    */

    'domain' => env('PLATFORM_DOMAIN', 'platform.localhost'),

];
