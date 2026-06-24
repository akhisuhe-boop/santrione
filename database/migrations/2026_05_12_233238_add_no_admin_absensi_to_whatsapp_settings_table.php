<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {

            $table->string('no_admin_absensi')
                ->nullable()
                ->after('sender');

        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_settings', function (Blueprint $table) {

            $table->dropColumn('no_admin_absensi');

        });
    }
};