<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kantin sekarang jadi entitas MANDIRI, bukan turunan Lembaga --
     * satu tenant (Yayasan) bisa punya beberapa kantin fisik, dan kantin
     * tidak wajib terikat ke 1 lembaga spesifik (mis. kantin bersama
     * yang melayani beberapa unit sekolah sekaligus).
     *
     * lembaga_id di sini SIFATNYA OPSIONAL -- cuma label/tag informatif
     * kalau memang mau menandai "kantin ini nominalnya milik lembaga X",
     * BUKAN scoping wajib. Tenant scope kantin lewat yayasan_id langsung.
     *
     * limit_tunai_kantin_harian pindah ke sini dari tabel lembagas
     * (kolomnya di lembagas dibiarkan ada, tidak dipakai lagi) karena
     * pembatasan tunai sekarang masuk akal per-kantin (per kasir/till),
     * bukan per-lembaga.
     */
    public function up(): void
    {
        Schema::create('kantins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('yayasan_id')
                ->constrained('yayasans')
                ->cascadeOnDelete();

            $table->foreignId('lembaga_id')
                ->nullable()
                ->constrained('lembagas')
                ->nullOnDelete();

            $table->string('nama');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('limit_tunai_kantin_harian')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kantins');
    }
};
