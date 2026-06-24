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
        Schema::create('setting_nominal_tagihans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_tagihan_id');
            $table->foreignId('tahun_ajaran_id');
            $table->foreignId('kelas_id')->nullable();
            $table->string('bulan')->nullable();
            $table->bigInteger('nominal');
            $table->timestamps();
                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_nominal_tagihans');
    }
};
