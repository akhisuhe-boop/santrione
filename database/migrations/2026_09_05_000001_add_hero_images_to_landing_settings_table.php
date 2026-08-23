<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            // Daftar path gambar buat slideshow Hero (bisa lebih dari 1).
            // TIDAK menghapus hero_mockup_gambar (single image lama) --
            // kalau field baru ini kosong, tampilan otomatis balik ke
            // field lama itu, baru ke kartu dashboard bawaan kalau
            // keduanya kosong. Jadi tidak ada yang "putus" buat data lama.
            $table->json('hero_images')->nullable()->after('hero_mockup_gambar');
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn('hero_images');
        });
    }
};
