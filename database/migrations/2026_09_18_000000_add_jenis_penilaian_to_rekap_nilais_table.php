<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. TAMBAH KOLOM JENIS PENILAIAN (KALAU BELUM ADA)
        |--------------------------------------------------------------------------
        | Idempotent: aman dijalankan ulang kalau attempt sebelumnya sempat
        | menambahkan kolom ini lalu gagal di step index.
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('rekap_nilais', 'jenis_penilaian')) {

            Schema::table('rekap_nilais', function (Blueprint $table) {

                // pts = Penilaian Tengah Semester
                // pas = Penilaian Akhir Semester

                $table->enum('jenis_penilaian', [
                    'pts',
                    'pas',
                ])
                    ->default('pas')
                    ->after('tahun_ajaran_id');

            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. BUAT UNIQUE INDEX BARU DULU (JANGAN DROP YANG LAMA DULU)
        |--------------------------------------------------------------------------
        | 'rekap_unique' lama masih dipakai MySQL untuk menopang foreign key
        | (siswa_id), jadi tidak bisa langsung di-drop. Index baru ini tetap
        | punya siswa_id di posisi paling kiri, jadi begitu dia ada, dia bisa
        | menggantikan peran index lama sebagai penopang FK tersebut.
        |--------------------------------------------------------------------------
        */

        if (! $this->indexExists('rekap_nilais', 'rekap_unique_pts_pas')) {

            Schema::table('rekap_nilais', function (Blueprint $table) {

                $table->unique([
                    'siswa_id',
                    'kelas_id',
                    'mapel_id',
                    'tahun_ajaran_id',
                    'jenis_penilaian',
                ], 'rekap_unique_pts_pas');

            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3. BARU DROP UNIQUE LAMA
        |--------------------------------------------------------------------------
        */

        if ($this->indexExists('rekap_nilais', 'rekap_unique')) {

            Schema::table('rekap_nilais', function (Blueprint $table) {
                $table->dropUnique('rekap_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->indexExists('rekap_nilais', 'rekap_unique')) {

            Schema::table('rekap_nilais', function (Blueprint $table) {

                $table->unique([
                    'siswa_id',
                    'kelas_id',
                    'mapel_id',
                    'tahun_ajaran_id',
                ], 'rekap_unique');

            });
        }

        if ($this->indexExists('rekap_nilais', 'rekap_unique_pts_pas')) {

            Schema::table('rekap_nilais', function (Blueprint $table) {
                $table->dropUnique('rekap_unique_pts_pas');
            });
        }

        if (Schema::hasColumn('rekap_nilais', 'jenis_penilaian')) {

            Schema::table('rekap_nilais', function (Blueprint $table) {
                $table->dropColumn('jenis_penilaian');
            });
        }
    }

    /**
     * Cek apakah sebuah index (unique/biasa) ada di tabel tertentu.
     * Dipakai supaya migration ini aman dijalankan berkali-kali (idempotent).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return count($result) > 0;
    }
};
