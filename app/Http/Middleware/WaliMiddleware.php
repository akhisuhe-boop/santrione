<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WaliMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('siswa_id')) {
            return redirect()->route('wali.login');
        }

        return $next($request);
    }
}