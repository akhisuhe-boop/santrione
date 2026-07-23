<?php

namespace App\Support;

/**
 * Daftar pusat fitur premium yang bisa dikunci/dibuka per paket
 * langganan (SubscriptionPlan::fitur). Tambah fitur baru di sini dulu
 * sebelum dipakai di Resource/Controller mana pun, supaya key-nya
 * konsisten dan gampang dilihat semua fitur premium yang ada.
 */
class FeatureGate
{
    public const PAYROLL = 'payroll';
    public const JADWAL_GENERATOR = 'jadwal_generator';
    public const GURU_PENGGANTI = 'guru_pengganti';
    public const WHATSAPP = 'whatsapp';
    public const CUSTOM_DOMAIN = 'custom_domain';

    /**
     * key => label tampilan (dipakai di form pilih fitur pada
     * SubscriptionPlanResource dan pesan "fitur terkunci").
     */
    public static function all(): array
    {
        return [
            self::PAYROLL => 'Payroll (Penggajian per JP & Tetap)',
            self::JADWAL_GENERATOR => 'Generate Jadwal Pelajaran Otomatis',
            self::GURU_PENGGANTI => 'Guru Pengganti (Portal Guru)',
            self::WHATSAPP => 'Notifikasi WhatsApp',
            self::CUSTOM_DOMAIN => 'Domain Custom per Yayasan',
        ];
    }

    public static function label(string $key): string
    {
        return static::all()[$key] ?? $key;
    }
}
