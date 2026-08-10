<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Plan "Paket Full" harganya sudah termasuk SEMUA modul — kalau
     * Lembaga tetap punya baris LembagaModule aktif (mis. Akademik,
     * Absensi, dst), TenantBillingCalculator harus menghitung modul
     * itu Rp0 (sudah termasuk), BUKAN menjumlah harga modul di atas
     * harga flat Paket Full (itu dobel hitung). Flag ini yang jadi
     * penandanya. Default false supaya plan lama/biasa tidak terpengaruh.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->boolean('termasuk_semua_modul')->default(false)->after('fitur');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('termasuk_semua_modul');
        });
    }
};
