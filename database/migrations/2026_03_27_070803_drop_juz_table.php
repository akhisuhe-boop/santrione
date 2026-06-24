<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('juz'); // 🔥 hapus tabel
    }

    public function down(): void
    {
        Schema::create('juz', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('total_ayat');
            $table->timestamps();
        });
    }
};