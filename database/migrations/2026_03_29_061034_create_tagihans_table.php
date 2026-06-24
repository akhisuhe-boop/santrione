<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();

            // Relasi ke siswa
            $table->foreignId('siswa_id')->constrained()->cascadeOnDelete();

            // Identitas tagihan
            $table->string('kode')->unique(); // INV-2026-0001
            $table->string('jenis'); // SPP, Daftar Ulang
            $table->string('judul'); // SPP Januari

            // Nominal
            $table->bigInteger('nominal');
            $table->bigInteger('nominal_terbayar')->default(0);

            // Tanggal
            $table->date('jatuh_tempo')->nullable();

            // Status
            $table->enum('status', ['belum', 'sebagian', 'lunas'])
                  ->default('belum');

            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Optional index biar cepat
            $table->index(['siswa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
