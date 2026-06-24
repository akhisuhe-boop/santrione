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
        Schema::create('rekap_nilais', function (Blueprint $table) {

        $table->id();

        $table->foreignId('siswa_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('kelas_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('mapel_id')
            ->constrained('mata_pelajarans')
            ->cascadeOnDelete();

        $table->foreignId('guru_id')
            ->constrained('pegawais')
            ->cascadeOnDelete();

        $table->foreignId('tahun_ajaran_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->double('nilai_akhir')
            ->nullable();

        $table->string('grade', 10)
            ->nullable();

        $table->text('deskripsi')
            ->nullable();

        $table->enum('status', [
            'draft',
            'publish',
        ])->default('draft');

        $table->timestamps();

        /*
        |--------------------------------------------------------------------------
        | UNIQUE
        |--------------------------------------------------------------------------
        | 1 siswa
        | 1 mapel
        | 1 semester
        | hanya boleh 1 rekap
        |--------------------------------------------------------------------------
        */

        $table->unique([

            'siswa_id',
            'kelas_id',
            'mapel_id',
            'tahun_ajaran_id',

        ], 'rekap_unique');

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekap_nilais');
    }
};
