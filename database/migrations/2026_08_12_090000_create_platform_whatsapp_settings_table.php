<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singleton table (SELALU cuma 1 baris, id=1) -- pengaturan WA
     * Qinara sekarang bisa diedit dari panel, bukan cuma .env. Kolom
     * boleh kosong (nullable) supaya deploy pertama kali tidak error
     * -- WhatsappService::sendPlatform() fallback ke config()/.env
     * kalau baris ini belum ada/kosong, jadi TIDAK breaking untuk
     * server yang sudah terlanjur isi lewat .env sebelumnya.
     */
    public function up(): void
    {
        Schema::create('platform_whatsapp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('api_url')->nullable();
            $table->string('token')->nullable();
            $table->string('sender')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_whatsapp_settings');
    }
};
