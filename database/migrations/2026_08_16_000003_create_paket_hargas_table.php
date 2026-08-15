<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_hargas', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tagline')->nullable();
            $table->string('target_pasar')->nullable(); // contoh: "Pesantren Kecil"
            $table->unsignedBigInteger('harga_bulanan'); // dalam Rupiah
            $table->unsignedTinyInteger('diskon_tahunan_persen')->default(15);
            $table->boolean('is_recommended')->default(false);
            $table->json('fitur'); // [{label, included}, ...]
            $table->string('cta_text')->default('Hubungi via WhatsApp');
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_hargas');
    }
};
