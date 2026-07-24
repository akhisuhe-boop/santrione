<?php

namespace App\Support;

/**
 * Daftar pusat fitur premium yang bisa dikunci/dibuka per paket
 * langganan (SubscriptionPlan::fitur).
 *
 * Setiap key di sini = 1 GRUP MENU di sidebar (bukan fitur granular
 * per-tombol seperti versi sebelumnya) — jadi checklist di form Paket
 * Langganan persis mengikuti grup yang dilihat user di sidebar kiri.
 * Kalau 1 grup tidak dicentang, SEMUA resource di dalam grup itu ikut
 * hilang dari sidebar tenant yang pakai paket tersebut.
 *
 * Tambah menu/grup baru di masa depan -> tambah 1 baris di sini +
 * pastikan $navigationGroup di Resource terkait PERSIS sama dengan
 * key di GROUP_MAP.
 */
class FeatureGate
{
    public const MASTER_DATA = 'master_data';
    public const MANAJEMEN_SEKOLAH = 'manajemen_sekolah';
    public const PSB = 'psb';
    public const KEUANGAN = 'keuangan';
    public const AKADEMIK = 'akademik';
    public const ABSENSI = 'absensi';
    public const TAHFIDZ = 'tahfidz';
    public const E_KANTIN = 'e_kantin';
    public const PERIZINAN = 'perizinan';
    public const KONSELING = 'konseling';
    public const MASTER_SETTING = 'master_setting';

    /**
     * key => label tampilan (dipakai di form pilih fitur pada
     * SubscriptionPlanResource dan pesan "fitur terkunci").
     */
    public static function all(): array
    {
        return [
            self::MASTER_DATA => 'Master Data',
            self::MANAJEMEN_SEKOLAH => 'Manajemen Sekolah',
            self::PSB => 'PSB',
            self::KEUANGAN => 'Keuangan',
            self::AKADEMIK => 'Akademik',
            self::ABSENSI => 'Absensi',
            self::TAHFIDZ => 'Tahfidz',
            self::E_KANTIN => 'e-Kantin',
            self::PERIZINAN => 'Perizinan',
            self::KONSELING => 'Konseling',
            self::MASTER_SETTING => 'Master Setting',
        ];
    }

    public static function label(string $key): string
    {
        return static::all()[$key] ?? $key;
    }

    /**
     * Nama $navigationGroup PERSIS seperti yang dideklarasikan di
     * masing-masing Resource -> key fitur di atas. "Platform (SaaS)"
     * sengaja TIDAK ada di sini — grup itu diatur lewat canViewAny()
     * is_platform_admin masing-masing Resource-nya sendiri, bukan
     * lewat langganan (paket tidak pernah membuka akses platform).
     */
    protected static function groupMap(): array
    {
        return [
            'Master Data' => self::MASTER_DATA,
            'Manajemen Sekolah' => self::MANAJEMEN_SEKOLAH,
            'PSB' => self::PSB,
            'Keuangan' => self::KEUANGAN,
            'Akademik' => self::AKADEMIK,
            'Absensi' => self::ABSENSI,
            'Tahfidz' => self::TAHFIDZ,
            'e-Kantin' => self::E_KANTIN,
            'Perizinan' => self::PERIZINAN,
            'Konseling' => self::KONSELING,
            'Master Setting' => self::MASTER_SETTING,
        ];
    }

    /**
     * Dipanggil dari BaseResource::canViewAny() — terjemahkan nama
     * $navigationGroup sebuah Resource jadi key fitur, kalau memang
     * terdaftar. Grup yang tidak ada di map (mis. "Platform (SaaS)",
     * atau resource tanpa navigationGroup) balikin null -> BaseResource
     * anggap TIDAK dikunci (tampil terus), supaya tidak ada resource
     * yang tiba-tiba hilang gara-gara lupa didaftarkan di sini.
     */
    public static function keyForNavigationGroup(?string $group): ?string
    {
        return static::groupMap()[$group] ?? null;
    }
}
