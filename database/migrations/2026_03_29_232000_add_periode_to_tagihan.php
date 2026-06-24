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
        Schema::table('tagihans', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();

            $table->foreignId('periode_tahun_ajaran_id')
            ->nullable()
            ->constrained('tahun_ajarans')
            ->nullOnDelete();

        $table->string('bulan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan', function (Blueprint $table) {
            //
        });
    }
};
