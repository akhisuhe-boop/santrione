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
        Schema::create('absensi', function (Blueprint $table) {
        $table->id();
        $table->foreignId('jadwal_kegiatan_id');
        $table->foreignId('siswa_id');
        $table->time('jam_scan');
        $table->string('status');
        $table->string('metode')->nullable();
        $table->unique(['jadwal_kegiatan_id','siswa_id']);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
