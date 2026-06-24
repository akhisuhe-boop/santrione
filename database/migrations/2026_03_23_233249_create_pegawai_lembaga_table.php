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
        Schema::create('pegawai_lembaga', function (Blueprint $table) {
        $table->id();

        $table->foreignId('pegawai_id')->constrained()->cascadeOnDelete();
        $table->foreignId('lembaga_id')->constrained()->cascadeOnDelete();

        $table->string('jabatan')->nullable();
        $table->string('status')->nullable(); // tetap / honorer

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai_lembaga');
    }
};
