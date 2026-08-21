<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom terpisah untuk fee admin Qinara (markup 0.75%, cap Rp10.000
     * -- lihat DokuService::hitungFee()). SENGAJA dipisah dari kolom
     * `nominal` supaya nominal_terbayar (dihitung dari SUM(nominal) di
     * Pembayaran::booted(), lihat app/Models/Pembayaran.php) tetap
     * murni mencerminkan pelunasan tagihan -- TIDAK ikut fee. Fee yang
     * sebenarnya dicharge ke wali murid = nominal + fee_admin, tapi
     * yang dihitung sebagai pelunasan tagihan cuma `nominal`.
     */
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unsignedInteger('fee_admin')->nullable()->after('nominal');
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn('fee_admin');
        });
    }
};
