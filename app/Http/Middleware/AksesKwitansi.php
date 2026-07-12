<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AksesKwitansi
{
    public function handle(Request $request, Closure $next): Response
    {
        // Admin Filament
        if (auth()->check()) {
            return $next($request);
        }

        // Wali
        if (session()->has('siswa_id')) {
            return $next($request);
        }

        // PPDB
        if (session()->has('ppdb_id')) {
            return $next($request);
        }

        return redirect()->route('login');
    }
}