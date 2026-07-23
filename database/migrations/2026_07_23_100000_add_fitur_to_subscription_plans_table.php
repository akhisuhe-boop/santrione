<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Daftar key fitur premium yang dibuka paket ini, mis.
            // ["payroll","jadwal_generator","guru_pengganti","whatsapp","custom_domain"].
            // Lihat App\Support\FeatureGate untuk daftar lengkap key
            // yang valid + label tampilannya.
            $table->json('fitur')->nullable()->after('maks_siswa');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('fitur');
        });
    }
};
