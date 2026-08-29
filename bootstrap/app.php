<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\DetectTenantDomain::class);


        $middleware->appendToGroup('web', \App\Http\Middleware\DetectTenantDomain::class);

        $middleware->alias([
            'wali' => \App\Http\Middleware\WaliMiddleware::class,
            'guru' => \App\Http\Middleware\GuruMiddleware::class,
            'ppdb' => \App\Http\Middleware\PpdbMiddleware::class,
            'akses.kwitansi' => \App\Http\Middleware\AksesKwitansi::class,
            'akses.slip.gaji' => \App\Http\Middleware\AksesSlipGaji::class,
        ]);

        // Webhook Midtrans, Xendit & DOKU dipanggil server-to-server
        // (bukan browser dengan session/CSRF token), jadi wajib
        // dikecualikan. /webhooks/doku sempat KELEWATAN di sini --
        // kemungkinan besar inilah sebab notifikasi DOKU tidak pernah
        // sampai ke DokuWebhookController::handle() sama sekali
        // (ditolak Laravel duluan dengan 419, sebelum kode kita
        // sempat jalan sama sekali).
        $middleware->validateCsrfTokens(except: [
            'webhooks/midtrans',
            'webhooks/xendit',
            'webhooks/doku',
            'webhooks/doku/token',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();