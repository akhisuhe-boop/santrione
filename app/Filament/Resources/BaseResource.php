<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;

/**
 * Base class untuk semua Resource.
 *
 * Kenapa ini perlu: Filament's native tenancy secara default otomatis
 * menerapkan scoping query berdasarkan relasi BelongsTo LANGSUNG ke model
 * tenant (Yayasan). Tapi banyak model di aplikasi ini terhubung ke Yayasan
 * secara TIDAK LANGSUNG (mis. Tagihan -> Siswa -> Lembaga -> Yayasan), jadi
 * mekanisme otomatis Filament itu tidak cocok/bisa keliru untuk kasus kita.
 *
 * Isolasi data TETAP terjaga penuh lewat trait BelongsToTenant yang sudah
 * dipasang di masing-masing Model (lihat Fase 1 roadmap). Baris di bawah
 * ini cuma mematikan lapisan auto-scoping bawaan Filament supaya tidak
 * dobel/konflik dengan scoping manual kita — Filament ->tenant() di sini
 * dipakai murni untuk kebutuhan URL routing & context switching, bukan
 * untuk isolasi data itu sendiri.
 */
abstract class BaseResource extends Resource
{
    protected static bool $isScopedToTenant = false;

    /**
     * Default label (judul singular) untuk resource ini.
     *
     * Filament secara default menebak label dari nama class model
     * (mis. "JurnalMengajar" -> "jurnal mengajar"). Di sini kita
     * pakai Str::headline() supaya rapi jadi Title Case tanpa perlu
     * mengisi $modelLabel manual di setiap Resource, tapi tetap
     * menghormati $modelLabel kalau memang sudah didefinisikan.
     */
    public static function getModelLabel(): string
    {
        if (filled(static::$modelLabel)) {
            return static::$modelLabel;
        }

        return str(class_basename(static::getModel()))
            ->headline()
            ->toString();
    }

    /**
     * Default label jamak (judul di navigasi/List/breadcrumb).
     *
     * Bahasa Indonesia TIDAK menambahkan akhiran "s" untuk bentuk
     * jamak (beda dengan Inggris). Filament bawaan otomatis
     * melakukan Str::plural() pada model label, yang menghasilkan
     * judul salah seperti "Jurnal Mengajars", "Asramas", dsb.
     * Di sini kita override supaya bentuk jamak = bentuk singular,
     * kecuali sebuah Resource memang sudah mengisi $pluralModelLabel
     * sendiri secara eksplisit.
     */
    public static function getPluralModelLabel(): string
    {
        if (filled(static::$pluralModelLabel)) {
            return static::$pluralModelLabel;
        }

        return static::getModelLabel();
    }

    /**
     * Helper buat Resource yang fiturnya dikunci per paket langganan
     * (lihat App\Support\FeatureGate). Platform admin selalu bisa
     * lihat (perlu buat support), tenant biasa ngikut fitur yang
     * dibuka paket yayasan mereka.
     *
     * Dipakai di Resource yang perlu dikunci, contoh:
     *
     *   public static function canViewAny(): bool
     *   {
     *       return static::tenantHasFeature(\App\Support\FeatureGate::PAYROLL);
     *   }
     */
    protected static function tenantHasFeature(string $key): bool
    {
        if (auth()->user()?->is_platform_admin) {
            return true;
        }

        $tenant = \Filament\Facades\Filament::getTenant();

        return (bool) $tenant?->hasFeature($key);
    }
}
