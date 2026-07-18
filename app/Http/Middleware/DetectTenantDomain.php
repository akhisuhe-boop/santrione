<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Yayasan;
use Illuminate\Support\Facades\Schema;

class DetectTenantDomain
{
    public function handle(Request $request, Closure $next)
    {
        if (
            Schema::hasTable('yayasans')
            && Schema::hasColumn('yayasans', 'domain')
            && ! session()->has('active_public_yayasan_id')
        ) {
            $host = strtolower(preg_replace('/^www\\./', '', $request->getHost()));

            $yayasan = Yayasan::withoutGlobalScopes()
                ->whereNotNull('domain')
                ->get()
                ->first(function ($y) use ($host) {
                    return strtolower(preg_replace('/^www\\./', '', $y->domain)) === $host;
                });

            if ($yayasan) {
                session(['active_public_yayasan_id' => $yayasan->id]);
            }
        }

        return $next($request);
    }
}
