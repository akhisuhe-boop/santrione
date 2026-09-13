<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DIUBAH -- skema harga baru (per-Yayasan, per-siswa) yang sudah
 * didiskusikan & di-ACC: `harga_bulanan` (dulu: flat per Lembaga per
 * bulan) sekarang berarti HARGA PER SISWA PER BULAN. Rename kolom
 * (bukan bikin kolom baru) karena belum ada Yayasan yang bayar
 * dengan skema lama sama sekali -- aman diganti langsung.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('module_prices', function (Blueprint $table) {
            $table->renameColumn('harga_bulanan', 'harga_per_siswa');
        });
    }

    public function down(): void
    {
        Schema::table('module_prices', function (Blueprint $table) {
            $table->renameColumn('harga_per_siswa', 'harga_bulanan');
        });
    }
};
