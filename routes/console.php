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

// Autopilot billing bulanan skema à la carte (Akses Platform + modul +
// diskon multi-lembaga) — tanggal 1 tiap bulan jam 02:00, sebelum cek
// langganan expired (01:00) supaya invoice baru sudah terbit duluan.
Schedule::command('subscription:generate-monthly-invoice')->monthlyOn(1, '02:00');

// Reminder WA H-7/H-3/H-1 sebelum trial habis — jam 09:00 (jam kerja,
// supaya notifikasi WA tidak masuk tengah malam).
Schedule::command('subscription:send-trial-reminders')->dailyAt('09:00');

// Hapus permanen Yayasan trial yang sudah habis >14 hari (default,
// bisa diatur lewat SUBSCRIPTION_PURGE_TRIAL_AFTER_DAYS) dan TIDAK
// PERNAH bayar sama sekali -- mingguan (bukan harian, penghapusan
// permanen tidak perlu buru-buru), Senin jam 03:00 (dini hari, di
// luar jam sibuk).
Schedule::command('subscription:purge-expired-trials')->weeklyOn(1, '03:00');

// Kirim broadcast terjadwal (CRM) yang jadwal_kirim-nya sudah tiba --
// dicek tiap 5 menit supaya broadcast "kirim sekarang" tidak nunggu lama.
Schedule::command('crm:kirim-broadcast-terjadwal')->everyFiveMinutes();
