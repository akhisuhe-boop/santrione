<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- pembukuan internal bisnis Qinara sendiri (kas
 * masuk/keluar operasional: revenue langganan, biaya operasional,
 * gaji, dll), TERPISAH TOTAL dari tabel `kas` yang sudah ada (itu
 * kas per-Yayasan/sekolah client, pakai BelongsToTenant). Tabel di
 * sini SENGAJA tidak ada kolom yayasan_id/lembaga_id sama sekali --
 * ini satu pembukuan tunggal milik Qinara sebagai perusahaan, bukan
 * per-tenant.
 *
 * Model laporan yang diminta: kas masuk/keluar + kategori + laporan
 * laba-rugi sederhana (BUKAN double-entry/neraca) -- jadi cukup 2
 * tabel: kategori (nama + tipe) dan transaksi (tanggal, tipe,
 * kategori, nominal, keterangan, siapa yang input).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qinara_kas_kategoris', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('qinara_kas_transaksis', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->enum('tipe', ['masuk', 'keluar']);
            $table->foreignId('kategori_id')->constrained('qinara_kas_kategoris')->restrictOnDelete();
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->foreignId('diinput_oleh_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tanggal', 'tipe']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qinara_kas_transaksis');
        Schema::dropIfExists('qinara_kas_kategoris');
    }
};
