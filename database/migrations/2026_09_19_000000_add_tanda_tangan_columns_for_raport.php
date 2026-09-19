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
        /*
        |--------------------------------------------------------------------------
        | TANDA TANGAN WALI KELAS (DISIMPAN DI PEGAWAI)
        |--------------------------------------------------------------------------
        | Path file gambar scan tanda tangan (PNG transparan), disimpan di
        | storage disk yang sama dengan foto/logo (r2-public).
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('pegawais', 'tanda_tangan')) {

            Schema::table('pegawais', function (Blueprint $table) {

                $table->string('tanda_tangan')
                    ->nullable()
                    ->after('foto');

            });
        }

        /*
        |--------------------------------------------------------------------------
        | TANDA TANGAN KEPALA SEKOLAH (DISIMPAN DI LEMBAGA)
        |--------------------------------------------------------------------------
        | Kepala sekolah di sistem ini cuma nama teks (kolom kepala_sekolah),
        | bukan relasi ke Pegawai -- jadi tanda tangannya juga disimpan
        | langsung di tabel lembagas.
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('lembagas', 'tanda_tangan_kepala_sekolah')) {

            Schema::table('lembagas', function (Blueprint $table) {

                $table->string('tanda_tangan_kepala_sekolah')
                    ->nullable()
                    ->after('kepala_sekolah');

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pegawais', 'tanda_tangan')) {

            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropColumn('tanda_tangan');
            });
        }

        if (Schema::hasColumn('lembagas', 'tanda_tangan_kepala_sekolah')) {

            Schema::table('lembagas', function (Blueprint $table) {
                $table->dropColumn('tanda_tangan_kepala_sekolah');
            });
        }
    }
};
