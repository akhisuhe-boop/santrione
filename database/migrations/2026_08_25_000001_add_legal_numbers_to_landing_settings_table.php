<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->string('nomor_nib')->nullable()->after('footer_legalitas');
            $table->string('nomor_akta')->nullable()->after('nomor_nib');
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn(['nomor_nib', 'nomor_akta']);
        });
    }
};
