<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tahfidz_targets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('siswa_id')->constrained()->cascadeOnDelete();
        $table->integer('target_juz'); // contoh: 30
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahfidz_targets');
    }
};
