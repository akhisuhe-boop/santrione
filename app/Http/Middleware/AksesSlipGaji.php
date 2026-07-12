<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AksesSlipGaji
{
    public function handle(Request $request, Closure $next): Response
    {
        // Admin Filament
        if (auth()->check()) {
            return $next($request);
        }

        $payroll = $request->route('payroll');

        // Guru hanya boleh melihat slip miliknya
        if (
            session()->has('guru_id') &&
            $payroll &&
            $payroll->pegawai_id === session('guru_id')
        ) {
            return $next($request);
        }

        return redirect()->route('guru.login');
    }
}