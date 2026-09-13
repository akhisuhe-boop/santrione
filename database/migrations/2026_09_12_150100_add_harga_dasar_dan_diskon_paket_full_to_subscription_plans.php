<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- 2 kolom baru untuk skema harga per-Yayasan/per-siswa:
 *
 * - harga_dasar_per_siswa: biaya "Akses Platform" dasar per siswa per
 *   bulan, SELALU berlaku (berapa pun modul berbayar yang aktif,
 *   termasuk 0) -- pengganti pendapatan dari modul Keuangan & e-Kantin
 *   yang digratiskan ke sekolah.
 * - diskon_paket_full_persen: diskon EKSTRA (di atas diskon volume
 *   siswa) khusus kalau Yayasan ambil Paket Full (semua modul
 *   berbayar aktif sekaligus). Paket Full TETAP dihitung dari rumus
 *   normal (dasar + semua modul), bukan harga flat terpisah -- supaya
 *   otomatis ikut menyesuaikan kalau harga modul/dasar berubah.
 *
 * Kolom lama yang terkait skema kuota/tier per-Lembaga (maks_siswa,
 * maks_lembaga, harga_per_siswa_tambahan, harga_per_lembaga_tambahan)
 * SENGAJA DIBIARKAN ADA, cuma berhenti dibaca kalkulator baru --
 * lebih aman daripada dihapus (tidak beresiko ada kode lain yang
 * masih baca kolom itu).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('harga_dasar_per_siswa')->nullable()->after('harga_bulanan');
            $table->unsignedTinyInteger('diskon_paket_full_persen')->nullable()->after('diskon_tahunan_persen');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['harga_dasar_per_siswa', 'diskon_paket_full_persen']);
        });
    }
};
