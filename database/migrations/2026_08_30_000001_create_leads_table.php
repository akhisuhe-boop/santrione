<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            // Nullable & tanpa foreign key keras -- kalau Yayasan-nya
            // suatu saat dihapus (misal trial expired lalu di-purge),
            // riwayat lead tetap ada untuk analisis, tidak ikut hilang.
            $table->unsignedBigInteger('yayasan_id')->nullable();
            $table->string('nama_lembaga');
            $table->string('nama_pic')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('sumber')->default('Trial Signup');
            $table->string('status')->default('baru'); // baru, dihubungi, follow_up, deal, batal
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
