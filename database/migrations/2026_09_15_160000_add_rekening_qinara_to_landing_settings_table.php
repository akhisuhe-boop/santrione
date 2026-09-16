<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- rekening tujuan transfer manual Qinara (BEDA dari
 * tabel `rekenings`/`lembaga_rekenings` yang sudah ada -- itu rekening
 * milik SEKOLAH untuk terima SPP dari wali murid; ini rekening
 * QINARA SENDIRI untuk terima pembayaran langganan dari Yayasan).
 * Ditaruh di landing_settings karena sifatnya sama: 1 baris
 * pengaturan global, bukan per-Yayasan/per-Lembaga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->string('rekening_bank')->nullable();
            $table->string('rekening_nomor')->nullable();
            $table->string('rekening_nama_pemilik')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn(['rekening_bank', 'rekening_nomor', 'rekening_nama_pemilik']);
        });
    }
};
