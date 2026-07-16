<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sebelumnya, sistem mendeteksi "ini tagihan pendaftaran/daftar ulang
     * PPDB" dengan mencocokkan teks nama/kode secara PERSIS (kode
     * 'pendaftaran_ppdb', 'formulir_pendaftaran', 'ppdb_daftar_ulang',
     * atau nama persis 'Pendaftaran'/'Daftar Ulang' -- 3 konvensi
     * berbeda di 3 tempat berbeda!). Ini rapuh: begitu yayasan lain
     * menamai jenis tagihannya sedikit berbeda, seluruh alur otomatis
     * PPDB (generate tagihan, update status, catat kas) diam-diam
     * berhenti bekerja.
     *
     * Diganti dengan 1 kolom eksplisit `tipe_sistem` yang tidak
     * tergantung nama tampilan sama sekali.
     */
    public function up(): void
    {
        Schema::table('jenis_tagihans', function (Blueprint $table) {
            $table->string('tipe_sistem')->nullable()->after('kode');
        });

        // Backfill data lama berdasarkan kode/nama yang sudah ada,
        // supaya yayasan yang sudah punya data tidak perlu setting ulang.
        DB::table('jenis_tagihans')
            ->whereIn('kode', ['pendaftaran_ppdb', 'formulir_pendaftaran'])
            ->orWhere('nama', 'Pendaftaran')
            ->update(['tipe_sistem' => 'pendaftaran_ppdb']);

        DB::table('jenis_tagihans')
            ->whereIn('kode', ['ppdb_daftar_ulang'])
            ->orWhere('nama', 'Daftar Ulang')
            ->update(['tipe_sistem' => 'daftar_ulang_ppdb']);

        // Unique per yayasan -- 1 yayasan cuma boleh punya 1 jenis
        // tagihan untuk masing-masing peran sistem. MySQL memperlakukan
        // NULL sebagai "berbeda" satu sama lain, jadi baris yang belum
        // di-set (tipe_sistem null) tidak akan saling tabrakan.
        Schema::table('jenis_tagihans', function (Blueprint $table) {
            $table->unique(['yayasan_id', 'tipe_sistem']);
        });
    }

    public function down(): void
    {
        Schema::table('jenis_tagihans', function (Blueprint $table) {
            $table->dropUnique(['yayasan_id', 'tipe_sistem']);
            $table->dropColumn('tipe_sistem');
        });
    }
};
