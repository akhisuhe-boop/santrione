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
        Schema::create('absensi_mapels', function (Blueprint $table) {

            $table->id();

            $table->foreignId('jadwal_pelajaran_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('siswa_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('tanggal');

            $table->enum('status', [
                'Hadir',
                'Izin',
                'Sakit',
                'Alpha',
            ])->default('Hadir');

            $table->text('keterangan')->nullable();

            // guru / user yg mengabsen
            $table->foreignId('diabsen_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // cegah duplicate absensi
            $table->unique([
                'jadwal_pelajaran_id',
                'siswa_id',
                'tanggal'
            ], 'absensi_mapel_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_mapels');
    }
};