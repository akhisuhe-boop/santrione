<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Panel Platform (platform.qinaraindonesia.id) memuat data lintas-
 * Yayasan (MRR, daftar semua Yayasan, harga modul, dll) — TIDAK boleh
 * diakses user Yayasan biasa sama sekali, beda dari resource-level
 * check (canViewAny() dsb) yang cuma menyembunyikan menu tapi masih
 * bisa ditembus lewat URL langsung. Middleware ini jadi pagar di
 * level panel: user login tapi bukan platform admin -> ditolak keras
 * (403), bukan cuma disembunyikan dari navigasi.
 */
class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->is_platform_admin) {
            abort(403, 'Halaman ini khusus untuk Platform Admin Qinara.');
        }

        return $next($request);
    }
}
