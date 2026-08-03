<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_harians', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal');

            $table->foreignId('siswa_id')
                ->nullable()
                ->constrained('siswas')
                ->cascadeOnDelete();

            $table->foreignId('pegawai_id')
                ->nullable()
                ->constrained('pegawais')
                ->cascadeOnDelete();

            $table->enum('tipe', ['siswa', 'guru']);

            $table->dateTime('jam_masuk')->nullable();
            $table->enum('status_masuk', ['Hadir', 'Terlambat'])->nullable();
            $table->string('metode_masuk')->nullable();

            $table->dateTime('jam_pulang')->nullable();
            $table->enum('status_pulang', ['Pulang', 'Pulang Awal'])->nullable();
            $table->string('metode_pulang')->nullable();

            $table->text('keterangan')->nullable();

            // diinput manual oleh admin (kalau bukan hasil scan)
            $table->foreignId('diinput_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['tanggal', 'siswa_id']);
            $table->unique(['tanggal', 'pegawai_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_harians');
    }
};
