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
        Schema::table('kas', function (Blueprint $table) {
            $table->foreignId('kategori_id')
        ->nullable()
        ->constrained('kategori_kas')
        ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kas', function (Blueprint $table) {
            //
        });
    }
};
