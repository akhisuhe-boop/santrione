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
        Schema::create('template_kegiatan', function (Blueprint $table) {
        $table->id();
        $table->string('nama_kegiatan');
        $table->string('hari');
        $table->time('jam_mulai');
        $table->time('jam_selesai');
        $table->integer('toleransi_telat')->default(0);
        $table->string('metode_scan')->nullable();
        $table->boolean('aktif')->default(true);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_kegiatan');
    }
};
