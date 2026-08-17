<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->boolean('promo_aktif')->default(false)->after('crm_notif_wa_numbers');
            $table->string('promo_teks')->nullable()->after('promo_aktif');
            $table->unsignedTinyInteger('promo_persen')->nullable()->after('promo_teks');
            $table->timestamp('promo_berakhir_pada')->nullable()->after('promo_persen');
            $table->unsignedTinyInteger('tahunan_diskon_persen')->default(15)->after('promo_berakhir_pada');
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn(['promo_aktif', 'promo_teks', 'promo_persen', 'promo_berakhir_pada', 'tahunan_diskon_persen']);
        });
    }
};
