<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dulu SubscriptionPlan cuma "harga_bulanan" flat, tanpa cara
     * bicara soal biaya tambahan siswa/lembaga di luar kuota
     * (maks_siswa/maks_lembaga). Kolom ini melengkapi itu — dipakai
     * SubscriptionPlan yang berperan sebagai "Akses Platform" (basis
     * biaya dasar) di skema baru, lihat App\Services\TenantBillingCalculator.
     *
     * Nullable & default null supaya plan lama (all-or-nothing, tanpa
     * biaya tambahan) tetap jalan apa adanya — kolom ini murni opsional.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('harga_per_siswa_tambahan')->nullable()->after('maks_siswa');
            $table->unsignedInteger('harga_per_lembaga_tambahan')->nullable()->after('maks_lembaga');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['harga_per_siswa_tambahan', 'harga_per_lembaga_tambahan']);
        });
    }
};
