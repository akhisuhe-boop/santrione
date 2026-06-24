<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perizinans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')->constrained()->cascadeOnDelete();

            $table->enum('tipe', ['keluar', 'pulang']);

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();

            $table->text('keperluan');

            $table->string('penjemput')->nullable();
            $table->string('hubungan')->nullable();
            $table->string('no_wa')->nullable();

            $table->enum('status', [
                'pending',
                'approved',
                'ditolak',
                'dijemput',
                'kembali',
                'selesai'
            ])->default('approved');

            $table->timestamp('waktu_keluar')->nullable();
            $table->timestamp('waktu_kembali')->nullable();

            $table->enum('keterangan_waktu', [
                'tepat_waktu',
                'terlambat',
                'sangat_terlambat'
            ])->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perizinans');
    }
};
