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
}
