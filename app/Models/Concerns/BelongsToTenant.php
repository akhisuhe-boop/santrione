<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Pasang trait ini ke semua model yang datanya harus terisolasi per yayasan.
 *
 * Cara pakai standar (model punya relasi lembaga() langsung, mis. Siswa, Kas, Kelas):
 *
 *     use App\Models\Concerns\BelongsToTenant;
 *
 *     class Siswa extends Model
 *     {
 *         use BelongsToTenant;
 *     }
 *
 * Kalau relasi ke Lembaga tidak langsung (mis. Tagihan -> siswa -> lembaga),
 * atau model itu sendiri adalah Lembaga (relasinya ke Yayasan, bukan ke Lembaga),
 * override applyTenantScope() di model tsb. Lihat contoh di app/Models/Lembaga.php
 * dan app/Models/Tagihan.php.
 */
trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = Auth::user();

            // Tidak ada user login (mis. dari command/queue tanpa context) →
            // tidak diterapkan di sini secara diam-diam. Proses background HARUS
            // set tenant context secara eksplisit (lihat catatan di Fase 5 roadmap).
            if (! $user) {
                return;
            }

            // Platform admin (kamu, pemilik SaaS): kalau lagi "masuk" ke
            // 1 yayasan tertentu lewat dropdown tenant di panel, data
            // yang tampil DIBATASI ke yayasan itu saja juga — supaya
            // monitoring per-yayasan akurat (sama seperti user biasa
            // saat itu). Cuma dibebaskan dari scope kalau memang belum
            // ada tenant yang dipilih (mis. buka resource lintas-tenant
            // atau proses command/queue), untuk keperluan support.
            if ($user->is_platform_admin) {

                $tenant = null;

                try {
                    $tenant = \Filament\Facades\Filament::getTenant();
                } catch (\Throwable $e) {
                    // Bukan lagi di dalam request panel Filament (mis.
                    // command/queue) — anggap tidak ada tenant terpilih.
                }

                if ($tenant) {
                    static::applyTenantScope($builder, $tenant->id);
                }

                return;
            }

            // User yang belum terhubung ke yayasan manapun → jangan tampilkan
            // apapun, JANGAN diloloskan. Ini default yang aman (fail-closed),
            // bukan fail-open.
            if (empty($user->yayasan_id)) {
                $builder->whereRaw('1 = 0');
                return;
            }

            static::applyTenantScope($builder, $user->yayasan_id);
        });

        // Auto-isi lembaga_id saat create, kalau model punya kolom itu dan
        // belum diisi manual, ambil dari lembaga yang sedang aktif di session
        // (context "sekolah mana yang lagi dikerjakan" dalam 1 yayasan).
        static::creating(function ($model) {
            if (
                array_key_exists('lembaga_id', $model->getAttributes()) === false
                && $model->isFillable('lembaga_id') === false
            ) {
                return;
            }

            if (empty($model->lembaga_id) && session()->has('active_lembaga_id')) {
                $model->lembaga_id = session('active_lembaga_id');
            }
        });
    }

    /**
     * Default: model diasumsikan punya relasi lembaga() langsung
     * (belongsTo Lembaga), dan Lembaga punya kolom yayasan_id.
     *
     * Override method ini di model yang path relasinya berbeda.
     */
    protected static function applyTenantScope(Builder $builder, int $yayasanId): void
    {
        $builder->whereHas('lembaga', function (Builder $q) use ($yayasanId) {
            $q->where('yayasan_id', $yayasanId);
        });
    }
}
