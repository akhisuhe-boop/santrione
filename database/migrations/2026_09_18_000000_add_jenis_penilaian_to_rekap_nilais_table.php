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
        Schema::table('rekap_nilais', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | JENIS PENILAIAN
            |--------------------------------------------------------------------------
            | pts = Penilaian Tengah Semester
            | pas = Penilaian Akhir Semester
            |--------------------------------------------------------------------------
            */

            $table->enum('jenis_penilaian', [
                'pts',
                'pas',
            ])
                ->default('pas')
                ->after('tahun_ajaran_id');

        });

        /*
        |--------------------------------------------------------------------------
        | GANTI UNIQUE CONSTRAINT
        |--------------------------------------------------------------------------
        | Sebelumnya: 1 siswa + 1 mapel + 1 tahun_ajaran = 1 rekap.
        | Sekarang: 1 siswa + 1 mapel + 1 tahun_ajaran + 1 jenis_penilaian = 1 rekap,
        | supaya rekap PTS dan PAS bisa hidup berdampingan.
        |--------------------------------------------------------------------------
        */

        Schema::table('rekap_nilais', function (Blueprint $table) {
            $table->dropUnique('rekap_unique');
        });

        Schema::table('rekap_nilais', function (Blueprint $table) {
            $table->unique([
                'siswa_id',
                'kelas_id',
                'mapel_id',
                'tahun_ajaran_id',
                'jenis_penilaian',
            ], 'rekap_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekap_nilais', function (Blueprint $table) {
            $table->dropUnique('rekap_unique');
        });

        Schema::table('rekap_nilais', function (Blueprint $table) {
            $table->unique([
                'siswa_id',
                'kelas_id',
                'mapel_id',
                'tahun_ajaran_id',
            ], 'rekap_unique');
        });

        Schema::table('rekap_nilais', function (Blueprint $table) {
            $table->dropColumn('jenis_penilaian');
        });
    }
};
