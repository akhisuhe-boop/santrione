<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- kebutuhan bisnis: satu Lembaga bisa punya rekening
 * berbeda per kegiatan (mis. PPDB rekening A, SPP & Daftar Ulang
 * rekening B) -- sebelumnya arsitektur cuma menampung 1 sub-account per
 * Lembaga (kolom doku_sub_account_id dkk langsung di tabel `lembagas`).
 *
 * Tabel ini TIDAK menggantikan kolom doku_* di `lembagas` -- kolom lama
 * tetap dipakai sebagai rekening "default"/fallback (supaya Lembaga yang
 * sudah terlanjur didaftarkan, seperti data testing sebelumnya, TETAP
 * jalan tanpa migrasi data apapun). Tabel baru ini cuma dipakai untuk
 * Lembaga yang BUTUH pisah rekening per kategori kegiatan.
 *
 * `kategori` sengaja string bebas (bukan enum kaku) -- nilai yang
 * dipakai sekarang: 'ppdb', 'spp' (lihat Lembaga::rekeningUntuk() untuk
 * pemetaan dari JenisTagihan.tipe_sistem ke kategori ini). Boleh
 * ditambah kategori baru kapan saja tanpa migration ulang.
 *
 * Setiap Lembaga+kategori cuma boleh 1 rekening aktif (unique).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembaga_rekenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained()->cascadeOnDelete();
            $table->string('kategori', 32); // 'ppdb', 'spp', dst -- lihat catatan class
            $table->string('nama', 128)->nullable(); // label bebas untuk admin, mis. "Rekening PPDB Al-Mubarok"
            $table->string('doku_sub_account_id')->nullable(); // profileId child (SAC-xxx), parent = doku_sub_account_id milik Lembaga
            $table->string('doku_account_no', 10)->nullable();
            $table->string('doku_split_rule_id', 64)->nullable(); // rule PERCENTAGE
            $table->string('doku_split_rule_id_flat', 64)->nullable(); // rule FLAT (cap)
            $table->string('doku_status')->default('menunggu_verifikasi');
            $table->timestamps();

            $table->unique(['lembaga_id', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembaga_rekenings');
    }
};
