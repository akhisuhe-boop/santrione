<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cek tiap hari jam 01:00 — suspend yayasan yang trial-nya habis atau
// langganannya lewat masa tenggang tanpa diperpanjang.
Schedule::command('subscription:check-expired')->dailyAt('01:00');
