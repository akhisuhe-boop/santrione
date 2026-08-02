<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Helper untuk resolve URL file yang bisa ada di 2 tempat:
 * - File baru: sudah di R2 (r2-public / r2-private)
 * - File lama: masih di disk lokal 'public' (diupload sebelum migrasi ke R2)
 *
 * Supaya file yang diupload sebelum migrasi tetap bisa dibuka,
 * tanpa perlu migrasi data satu-satu.
 */
class FileUrlResolver
{
    /**
     * Resolve URL untuk file privat (butuh signed/temporary URL di R2).
     */
    public static function private(?string $path, int $expiresInMinutes = 10): ?string
    {
        if (! $path) {
            return null;
        }

        try {
            if (Storage::disk('r2-private')->exists($path)) {
                return Storage::disk('r2-private')->temporaryUrl(
                    $path,
                    now()->addMinutes($expiresInMinutes)
                );
            }
        } catch (\Throwable $e) {
            // lanjut ke fallback lokal
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        return null;
    }

    /**
     * Resolve URL untuk file publik (logo, foto, gambar produk, dsb).
     */
    public static function public(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        try {
            if (Storage::disk('r2-public')->exists($path)) {
                return Storage::disk('r2-public')->url($path);
            }
        } catch (\Throwable $e) {
            // lanjut ke fallback lokal
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        return null;
    }
}
