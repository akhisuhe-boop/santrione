<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// PENTING: cek dulu apakah route '/' sudah dipakai di web.php / middleware
// DetectTenantDomain sebelum menambahkan ini. Lihat INSTRUKSI-INSTALASI.md.
Route::get('/', [LandingController::class, 'index'])->name('landing');
