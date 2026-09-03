<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PIN opsional per kantin -- kalau diisi, kasir wajib masukkan PIN
     * yang benar dulu sebelum bisa mengoperasikan kantin itu di halaman
     * Kasir. Disimpan dalam bentuk hash (bukan plain text), sama seperti
     * password pada umumnya. Kosong = tidak ada PIN, kantin bisa
     * langsung dipilih tanpa verifikasi (perilaku lama, backward
     * compatible).
     */
    public function up(): void
    {
        Schema::table('kantins', function (Blueprint $table) {
            $table->string('pin')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('kantins', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
