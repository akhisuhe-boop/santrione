<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studi_kasus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lembaga');
            $table->string('badge_text')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable(); // path di disk r2-public
            $table->json('stats')->nullable(); // [{label, value}, ...] -- semua diisi manual, tidak dihitung otomatis
            $table->text('kutipan')->nullable();
            $table->string('kutipan_nama')->nullable();
            $table->string('kutipan_jabatan')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studi_kasus');
    }
};
