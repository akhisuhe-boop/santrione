<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_mengajars', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELATION
            |--------------------------------------------------------------------------
            */

            // Guru
            $table->foreignId('pegawai_id')
                ->constrained('pegawais')
                ->cascadeOnDelete();

            // Penempatan Guru di Lembaga
            $table->foreignId('pegawai_lembaga_id')
                ->nullable()
                ->constrained('pegawai_lembaga')
                ->nullOnDelete();

            // Kelas
            $table->foreignId('kelas_id')
                ->constrained('kelas')
                ->cascadeOnDelete();

            // Jadwal Pelajaran
            $table->foreignId('jadwal_pelajaran_id')
                ->nullable()
                ->constrained('jadwal_pelajarans')
                ->nullOnDelete();

            // Mata Pelajaran
            $table->foreignId('mata_pelajaran_id')
                ->nullable()
                ->constrained('mata_pelajarans')
                ->nullOnDelete();

            // Jam Pelajaran
            $table->foreignId('jam_pelajaran_id')
                ->nullable()
                ->constrained('jam_pelajarans')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DATA JURNAL
            |--------------------------------------------------------------------------
            */

            $table->date('tanggal');

            $table->longText('materi');

            $table->enum('status', [
                'draft',
                'valid',
            ])->default('draft');

            /*
            |--------------------------------------------------------------------------
            | VALIDASI
            |--------------------------------------------------------------------------
            */

            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('validated_at')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | UNIQUE
            |--------------------------------------------------------------------------
            */

            $table->unique(
                [
                    'pegawai_id',
                    'jadwal_pelajaran_id',
                    'tanggal',
                ],
                'jurnal_unique_per_jadwal'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_mengajars');
    }
};