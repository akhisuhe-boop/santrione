<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // 'bulanan' | 'tahunan'. Default 'bulanan' -- baris lama
            // otomatis dianggap bulanan (perilaku sebelum fitur ini ada,
            // tidak ada yang berubah untuk mereka).
            $table->string('siklus_billing')->default('bulanan')->after('subscription_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('siklus_billing');
        });
    }
};
