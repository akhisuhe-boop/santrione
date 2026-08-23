<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            // 'manual'    = 1 tanggal tetap, sama untuk semua pengunjung
            //               (pakai promo_berakhir_pada seperti biasa).
            // 'evergreen' = tiap pengunjung dapat jendela waktunya sendiri,
            //               mulai dihitung dari kunjungan PERTAMA mereka
            //               (disimpan di browser masing-masing lewat
            //               localStorage, bukan di database) -- berjalan
            //               selama promo_evergreen_durasi_jam.
            $table->string('promo_mode')->default('manual')->after('promo_aktif');
            $table->unsignedInteger('promo_evergreen_durasi_jam')->default(24)->after('promo_berakhir_pada');
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn(['promo_mode', 'promo_evergreen_durasi_jam']);
        });
    }
};
