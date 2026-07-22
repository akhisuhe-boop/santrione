<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarif honor guru pengganti diset per Lembaga (tiap sekolah bisa
     * beda kebijakan), bukan diisi manual satu-satu tiap kali guru
     * mengisi jurnal mengajar pengganti. Kalau kosong (null),
     * PayrollService fallback memakai tarif per JP guru pengganti
     * itu sendiri.
     */
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->bigInteger('tarif_pengganti_per_jp')
                ->nullable()
                ->after('npsn');
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn('tarif_pengganti_per_jp');
        });
    }
};
