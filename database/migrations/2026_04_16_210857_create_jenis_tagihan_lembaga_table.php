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
        Schema::create('jenis_tagihan_lembaga', function (Blueprint $table) {
        $table->id();

        $table->foreignId('jenis_tagihan_id')->constrained()->cascadeOnDelete();
        $table->foreignId('lembaga_id')->constrained()->cascadeOnDelete();

        $table->bigInteger('nominal');
        $table->boolean('is_cicilan')->default(false);
        $table->integer('jatuh_tempo_hari')->nullable(); // 🔥 optional

        $table->timestamps();

        $table->unique(['jenis_tagihan_id', 'lembaga_id']);

        // 🔥 optimization
        $table->index('jenis_tagihan_id');
        $table->index('lembaga_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_tagihan_lembaga');
    }
};
