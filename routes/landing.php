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
Route::get('/landing-page', [LandingController::class, 'index'])->name('landing.preview');

// Halaman legal - dibuka dari footer, aktif di domain manapun (sama seperti
// /landing-page) supaya bisa dites tanpa domain utama aktif dulu.
Route::get('/kebijakan-privasi', [LegalController::class, 'privasi'])->name('legal.privasi');
Route::get('/syarat-ketentuan', [LegalController::class, 'syaratKetentuan'])->name('legal.syarat-ketentuan');
