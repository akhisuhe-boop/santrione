<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            // Kalau AKTIF: banner cuma tampilkan countdown timer polos
            // (tanpa ikon/judul/badge persen), DAN harga di kartu paket
            // TIDAK ikut didiskon sama sekali -- murni efek urgensi
            // (mis. "buruan sebelum gelombang 1 tutup"), terlepas dari
            // ada tidaknya promo harga sungguhan.
            $table->boolean('promo_hanya_countdown')->default(false)->after('promo_berakhir_pada');
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn('promo_hanya_countdown');
        });
    }
};
