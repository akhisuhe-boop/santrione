<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- kode promo diskon persen, diinput TENANT sendiri di
 * Checkout (beda dari "Promo Pendaftaran" yang sudah ada, itu
 * otomatis untuk semua pendaftar landing page).
 *
 * maks_pemakaian & berlaku_sampai SENGAJA nullable -- kosong berarti
 * "tidak terbatas"/"tidak ada expired", supaya 1 sistem ini bisa
 * dipakai untuk kode sekali-pakai, kode permanen mitra, MAUPUN kode
 * musiman, tinggal admin isi sesuai kebutuhan per-kode (lihat
 * PromoKode::valid() untuk logikanya).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_kodes', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('deskripsi')->nullable();
            $table->unsignedTinyInteger('diskon_persen');
            $table->unsignedInteger('maks_pemakaian')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_kodes');
    }
};
