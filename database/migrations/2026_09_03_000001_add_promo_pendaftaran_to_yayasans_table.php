<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {
            // Snapshot promo landing page SAAT Yayasan ini daftar (kalau
            // memang ada promo aktif waktu itu) -- terpisah total dari
            // LandingSetting->promo_* yang bisa berubah/berakhir kapan
            // saja setelahnya. Cuma berlaku untuk TAGIHAN PERTAMA Yayasan
            // ini (ditandai promo_pendaftaran_terpakai), tagihan
            // berikutnya otomatis kembali ke harga normal.
            $table->unsignedTinyInteger('promo_pendaftaran_persen')->nullable()->after('trial_ends_at');
            $table->string('promo_pendaftaran_teks')->nullable()->after('promo_pendaftaran_persen');
            $table->boolean('promo_pendaftaran_terpakai')->default(false)->after('promo_pendaftaran_teks');
        });
    }

    public function down(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {
            $table->dropColumn(['promo_pendaftaran_persen', 'promo_pendaftaran_teks', 'promo_pendaftaran_terpakai']);
        });
    }
};
