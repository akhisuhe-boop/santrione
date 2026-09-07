<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- kebutuhan: kategori rekening DOKU (untuk multi-rekening
 * per Lembaga) TIDAK BOLEH dikaitkan ke `tipe_sistem` (field "Peran
 * Sistem" yang sudah ada) -- field itu dipakai kode lain untuk logika
 * alur PPDB otomatis (lihat Tagihan.php yang query eksplisit
 * `tipe_sistem = 'pendaftaran_ppdb'`), jadi memaksanya menampung
 * kategori rekening bebas (uang gedung, seragam, dst) berisiko
 * mengacaukan logika itu.
 *
 * `kategori_rekening` berdiri sendiri, bebas diisi apa saja (slug bebas,
 * TIDAK terikat pilihan tetap) -- dipakai murni untuk routing pembayaran
 * ke rekening DOKU yang benar lewat Lembaga::rekeningUntuk(). Nullable:
 * kosong = ikut rekening "default" Lembaga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_tagihans', function (Blueprint $table) {
            $table->string('kategori_rekening', 32)->nullable()->after('tipe_sistem');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_tagihans', function (Blueprint $table) {
            $table->dropColumn('kategori_rekening');
        });
    }
};
