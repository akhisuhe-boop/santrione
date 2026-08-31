<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- keputusan bisnis: fee Qinara tetap "persentase dengan
 * cap" (0.85%, maks Rp8.500/transaksi) -- BUKAN diubah ke flat murni
 * atau persentase murni, supaya SPP bulanan (nominal kecil, sering)
 * tetap proporsional ringan buat wali, sementara PPDB/uang pangkal
 * (nominal besar, jarang) fee-nya dibatasi predictable buat sekolah.
 *
 * Karena 1 Split Rule DOKU cuma bisa PERCENTAGE MURNI atau FLAT MURNI
 * (tidak bisa "persentase dengan cap" dalam 1 rule -- lihat catatan
 * panjang di DokuService::buatSplitRule() versi sebelumnya), solusinya:
 * setiap Lembaga punya DUA Split Rule sekaligus (dibuat sekali saat
 * registrasi, sama-sama disimpan):
 * - `doku_split_rule_id`       -> rule PERCENTAGE (dipakai kalau
 *   0.85% x nominal <= cap)
 * - `doku_split_rule_id_flat`  -> rule FLAT senilai cap (dipakai kalau
 *   0.85% x nominal > cap)
 * Pemilihan rule mana yang dipakai dilakukan DINAMIS per transaksi
 * (lihat DokuService::pilihSplitRuleId()), bukan sekali per Lembaga.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->string('doku_split_rule_id_flat', 64)->nullable()->after('doku_split_rule_id');
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn('doku_split_rule_id_flat');
        });
    }
};
