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

// Tandai Alpa tiap hari jam 16:00 — siswa/guru yang punya jadwal hari itu
// tapi tidak scan absensi masuk dan tidak ada izin/sakit yang disetujui.
Schedule::command('absensi:tandai-alpa')->dailyAt('16:00');
