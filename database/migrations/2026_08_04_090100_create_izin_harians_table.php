<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_harians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')
                ->nullable()
                ->constrained('siswas')
                ->cascadeOnDelete();

            $table->foreignId('pegawai_id')
                ->nullable()
                ->constrained('pegawais')
                ->cascadeOnDelete();

            $table->enum('tipe', ['siswa', 'guru']);

            $table->enum('jenis', ['Izin', 'Sakit']);

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            $table->text('keterangan');

            $table->string('lampiran')->nullable();

            $table->enum('status', ['pending', 'approved', 'ditolak'])->default('pending');

            $table->foreignId('diajukan_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('diproses_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('catatan_admin')->nullable();

            $table->timestamp('diproses_pada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_harians');
    }
};
