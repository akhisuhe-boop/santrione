<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- catat setiap kali sebuah Yayasan berhasil PAKAI
 * (submit pembayaran, bukan cuma "cek/terapkan" di form) kode promo.
 * Ini yang jadi dasar hitung batas pemakaian per-Yayasan (bukan batas
 * pemakaian GLOBAL -- 1 kode bisa dipakai banyak Yayasan berbeda,
 * tapi tiap Yayasan kena batas pemakaiannya sendiri-sendiri).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_kode_pemakaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_kode_id')->constrained('promo_kodes')->cascadeOnDelete();
            $table->foreignId('yayasan_id')->constrained('yayasans')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['promo_kode_id', 'yayasan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_kode_pemakaians');
    }
};
