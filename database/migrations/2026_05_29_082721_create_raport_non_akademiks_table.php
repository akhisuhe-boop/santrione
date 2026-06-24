<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raport_non_akademiks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')
                ->constrained('siswas')
                ->cascadeOnDelete();

            $table->foreignId('kelas_id')
                ->nullable()
                ->constrained('kelas')
                ->nullOnDelete();

            $table->foreignId('tahun_ajaran_id')
                ->nullable()
                ->constrained('tahun_ajarans')
                ->nullOnDelete();

            $table->string('semester');

            $table->text('catatan_wali_kelas')
                ->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique([
                'siswa_id',
                'tahun_ajaran_id',
                'semester',
            ], 'raport_non_akademik_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_non_akademiks');
    }
};