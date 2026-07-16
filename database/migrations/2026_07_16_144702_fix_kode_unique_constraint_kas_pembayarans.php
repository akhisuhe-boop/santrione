<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sama seperti kasus kategori_kas & jenis_tagihans sebelumnya: kolom
     * 'kode' di tabel kas & pembayarans generate otomatis lewat HasKode
     * trait, yang query "kode terakhir"-nya SUDAH ter-scope per tenant.
     * Tapi constraint unique di database masih GLOBAL (1 kode cuma boleh
     * dipakai 1x se-seluruh aplikasi) — akibatnya 2 yayasan berbeda yang
     * sama-sama generate kode pertama mereka (KM-2026-0001) akan
     * tabrakan. Diubah jadi unique per lembaga_id.
     */
    public function up(): void
    {
        Schema::table('kas', function (Blueprint $table) {
            $table->dropUnique('kas_kode_unique');
            $table->unique(['lembaga_id', 'kode']);
        });

        // Tabel pembayarans TIDAK punya kolom lembaga_id langsung (scoping
        // tenant-nya lewat relasi siswa/ppdb), jadi tidak bisa dibuatkan
        // composite unique yang sama. Cukup hapus constraint unique global
        // -- kode di sini murni label referensi, bukan kunci relasi, jadi
        // aman kalau 2 yayasan kebetulan punya kode yang sama persis.
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropUnique('pembayarans_kode_unique');
        });

        // Sama seperti pembayarans, tagihans juga tidak punya lembaga_id
        // langsung (scoping lewat siswa/ppdb) dan generate 'kode' dengan
        // pola yang sama (INV-2026-XXXX).
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropUnique('tagihans_kode_unique');
        });

        // NIS (Nomor Induk Siswa) itu penomoran internal tiap sekolah,
        // BEDA dengan NISN/NIK yang memang identitas nasional. Wajar 2
        // sekolah berbeda pakai skema NIS yang sama (mis. sama-sama mulai
        // dari 001). Siswa punya lembaga_id langsung, jadi bisa dibuat
        // composite unique.
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropUnique('siswas_nis_unique');
            $table->unique(['lembaga_id', 'nis']);
        });
    }

    public function down(): void
    {
        Schema::table('kas', function (Blueprint $table) {
            $table->dropUnique(['lembaga_id', 'kode']);
            $table->unique('kode');
        });

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unique('kode');
        });

        Schema::table('tagihans', function (Blueprint $table) {
            $table->unique('kode');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropUnique(['lembaga_id', 'nis']);
            $table->unique('nis');
        });
    }
};
