<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('masalah_solusis', function (Blueprint $table) {
            $table->id();
            $table->string('teks_masalah'); // kolom kiri (sebelum)
            $table->string('teks_solusi'); // kolom kanan (sesudah), baris yang sama = pasangan
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('masalah_solusis');
    }
};
