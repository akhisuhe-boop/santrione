<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('pesan');
            $table->json('target_types'); // ['yayasan_semua'|'yayasan_trial'|'yayasan_aktif'|'lead_semua', ...]
            $table->timestamp('jadwal_kirim');
            $table->string('status')->default('terjadwal'); // terjadwal, terkirim, gagal
            $table->timestamp('dikirim_pada')->nullable();
            $table->unsignedInteger('jumlah_terkirim')->default(0);
            $table->text('pesan_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
