<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Role di sistem ini TETAP satu tabel global (tidak pakai fitur
     * "teams" bawaan Spatie/Permission — terlalu berisiko diaktifkan
     * di atas data production yang sudah berjalan, dan modelnya tidak
     * pas untuk kasus "sebagian role global, sebagian per-tenant").
     *
     * Sebagai gantinya: kolom yayasan_id ini MURNI penanda untuk
     * kebutuhan UI/manajemen —
     *   - NULL           = role global (mis. "Admin Yayasan",
     *                       "super_admin"), dipakai bersama semua
     *                       tenant, cuma Platform Admin yang boleh
     *                       kelola.
     *   - Terisi angka   = role custom milik 1 yayasan tertentu (mis.
     *                       "Keuangan", "Akademik"), cuma dikelola dan
     *                       kelihatan oleh yayasan itu sendiri.
     *
     * Penentuan siapa yang punya permission apa TETAP lewat mekanisme
     * asli Spatie (assign role ke user via role id) — kolom ini TIDAK
     * mengubah cara pengecekan permission jalan sama sekali, jadi
     * aman ditambahkan tanpa risiko ke fungsi yang sudah berjalan.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('yayasan_id')
                ->nullable()
                ->after('guard_name')
                ->constrained('yayasans')
                ->cascadeOnDelete();
        });

        // Rename role lama "Admin Sekolah" -> "Admin Yayasan" (kalau
        // sudah pernah dibuat sebelumnya di dev/production).
        DB::table('roles')
            ->where('name', 'Admin Sekolah')
            ->update(['name' => 'Admin Yayasan']);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('yayasan_id');
        });
    }
};
