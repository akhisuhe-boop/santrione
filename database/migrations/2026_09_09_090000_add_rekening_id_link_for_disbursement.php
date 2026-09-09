<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- persiapan Disbursement DOKU (payout ke rekening bank
 * asli sekolah). Sub-Account DOKU (lembagas.doku_* & lembaga_rekenings)
 * cuma ledger internal DOKU, TIDAK otomatis settle ke rekening bank
 * sekolah -- sudah dikonfirmasi via telepon dengan tim DOKU. Rekening
 * bank asli sekolah SUDAH ada di tabel `rekenings` (dipakai untuk
 * pembukuan Kas), tapi belum terhubung sama sekali ke sisi DOKU.
 *
 * Kolom `rekening_id` di sini murni REFERENSI/PENANDA "kalau kategori
 * DOKU ini nanti dicairkan, uangnya tujuannya ke baris Rekening yang
 * mana" -- TIDAK memengaruhi alur split rule atau pembuatan VA/QRIS
 * yang sudah jalan sama sekali (nullable, tidak dibaca oleh
 * DokuService/Lembaga::rekeningUntuk()). Baru dipakai nanti oleh job
 * Disbursement yang belum dibangun.
 *
 * Sengaja pakai foreign key eksplisit (bukan cocokkan nama/teks),
 * karena `lembaga_rekenings.kategori` bebas (mis. "ppdb", "uang_gedung",
 * "seragam" -- beda-beda tiap Lembaga) sementara `rekenings.keperluan`
 * cuma 2 opsi tetap (PPDB) -- tidak bisa diandalkan untuk pencocokan
 * otomatis lintas kategori apa pun yang dibuat sekolah.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Rekening kategori khusus (child sub-account) -- LembagaRekening
        Schema::table('lembaga_rekenings', function (Blueprint $table) {
            $table->foreignId('rekening_id')->nullable()
                ->after('kategori')
                ->constrained('rekenings')
                ->nullOnDelete();
        });

        // Rekening UTAMA/default Lembaga -- kolom doku_* langsung di
        // tabel lembagas, jadi butuh kolom penghubung yang sama di sini.
        Schema::table('lembagas', function (Blueprint $table) {
            $table->foreignId('rekening_id')->nullable()
                ->after('doku_status')
                ->constrained('rekenings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lembaga_rekenings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rekening_id');
        });

        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rekening_id');
        });
    }
};
