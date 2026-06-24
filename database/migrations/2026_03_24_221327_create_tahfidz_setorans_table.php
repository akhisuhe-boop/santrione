<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahfidz_setorans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained()->cascadeOnDelete();

            $table->date('tanggal');

            $table->enum('jenis', ['ziyadah', 'murajaah']);

            $table->string('surah');
            $table->integer('ayat_dari');
            $table->integer('ayat_sampai');
            $table->integer('jumlah_ayat')->nullable();

            $table->integer('nilai')->nullable(); // 0 - 100
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahfidz_setorans');
    }
};