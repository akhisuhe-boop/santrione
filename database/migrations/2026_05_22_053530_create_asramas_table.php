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
        Schema::create('asramas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('lembaga_id')
        ->nullable()
        ->constrained()
        ->nullOnDelete();
    $table->string('nama');
    $table->foreignId('wali_asrama_id')
        ->nullable()
        ->constrained('pegawais')
        ->nullOnDelete();
    $table->integer('kapasitas')->nullable();
    $table->text('keterangan')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asramas');
    }
};
