<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payroll sekarang bisa digenerate & dibayar TERPISAH per jenis
     * dalam 1 bulan yang sama -- "struktural" (jabatan dengan
     * metode_penggajian = tetap, mis. kepala sekolah/waka/staff) dan
     * "fungsional" (jabatan dengan metode_penggajian = per_jp, mis.
     * honor mengajar), supaya bisa dibayar di tanggal berbeda (mis.
     * struktural tanggal 5, fungsional tanggal 10).
     *
     * Nullable & tidak retroaktif -- payroll lama (sebelum fitur ini)
     * tetap jenis-nya null, diperlakukan sebagai "gabungan" (semua
     * jabatan digabung jadi 1, perilaku lama) supaya data historis
     * tidak berubah maknanya.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->string('jenis')->nullable()->after('tahun');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
