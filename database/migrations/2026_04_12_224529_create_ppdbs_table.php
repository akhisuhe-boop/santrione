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
    Schema::create('ppdbs', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

        $table->string('nama');
        $table->string('nisn')->nullable();
        $table->string('jenis_kelamin')->nullable();
        $table->string('no_hp')->nullable();
        $table->text('alamat')->nullable();

        $table->string('asal_sekolah')->nullable();
        $table->string('nama_ortu')->nullable();

        $table->string('status')->default('draft'); 
        // draft, menunggu_pembayaran, tes, lulus, tidak_lulus, aktif

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdbs');
    }
};
