<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- pengganti kolom jam_masuk_siswa/jam_pulang_siswa/
 * jam_masuk_guru/jam_pulang_guru/toleransi_telat_menit di tabel
 * `lembagas` (yang GENERAL, sama untuk semua hari). Sekarang per
 * HARI -- 1 baris per (lembaga, hari) yang benar-benar diatur admin.
 * Hari yang TIDAK punya baris di sini = TIDAK wajib absen sama
 * sekali hari itu (bukan dianggap Alpa) -- supaya tiap sekolah bisa
 * atur hari liburnya sendiri (ada yang Jumat, ada yang Sabtu-Minggu).
 *
 * Kolom lama di `lembagas` SENGAJA DIBIARKAN ADA (tidak dihapus) --
 * lebih aman, tidak beresiko ada kode lain yang masih baca kolom itu
 * tanpa saya sadari.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_absensi_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembagas')->cascadeOnDelete();
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']);
            $table->time('jam_masuk_siswa')->nullable();
            $table->time('jam_pulang_siswa')->nullable();
            $table->time('jam_masuk_guru')->nullable();
            $table->time('jam_pulang_guru')->nullable();
            $table->unsignedInteger('toleransi_telat_menit')->default(15);
            $table->timestamps();

            $table->unique(['lembaga_id', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_absensi_harians');
    }
};
