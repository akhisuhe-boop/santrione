<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

// Landing page publik - hanya aktif kalau diakses lewat domain utama
// (qinaraindonesia.id), tidak bentrok dengan '/' di app.qinaraindonesia.id
// atau dev.qinaraindonesia.id yang tetap redirect ke login.
Route::domain('qinaraindonesia.id')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
});

// Route tambahan untuk cek/preview landing page dari domain manapun
// (local, dev.qinaraindonesia.id, dst) tanpa perlu domain utama aktif dulu.
// Nama route TETAP 'landing.preview' (dipakai di beberapa blade lewat
// route()), yang berubah cuma path URL-nya: /landing-page -> /home.
Route::get('/home', [LandingController::class, 'index'])->name('landing.preview');

// Redirect URL lama, kalau-kalau sudah sempat dibagikan/di-bookmark orang.
Route::redirect('/landing-page', '/home', 301);

// Form "Jadwalkan Demo Gratis" (popup di landing page) -- submit di sini
// membuat Lead baru (masuk ke CRM/Platform panel > Leads) dan memicu
// notifikasi WA ke nomor admin (LandingSetting->crm_notif_wa_numbers),
// bukan lagi langsung buka WhatsApp pengunjung seperti sebelumnya.
Route::post('/landing/demo-request', [LandingController::class, 'demoRequest'])->name('landing.demo-request');

// Halaman legal - dibuka dari footer, aktif di domain manapun (sama seperti
// /landing-page) supaya bisa dites tanpa domain utama aktif dulu.
Route::get('/kebijakan-privasi', [LegalController::class, 'privasi'])->name('legal.privasi');
Route::get('/syarat-ketentuan', [LegalController::class, 'syaratKetentuan'])->name('legal.syarat-ketentuan');
