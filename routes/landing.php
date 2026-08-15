<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// Landing page publik - hanya aktif kalau diakses lewat domain utama
// (qinaraindonesia.id), tidak bentrok dengan '/' di app.qinaraindonesia.id
// atau dev.qinaraindonesia.id yang tetap redirect ke login.
Route::domain('qinaraindonesia.id')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing');
});

// Route tambahan untuk preview/testing landing page dari domain manapun
// (local, dev.qinaraindonesia.id, dst) tanpa perlu akses lewat domain
// qinaraindonesia.id yang sebenarnya.
Route::get('/preview-landing', [LandingController::class, 'index'])->name('landing.preview');