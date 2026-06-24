<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi_mapels', function (Blueprint $table) {
            $table->foreignId('jurnal_mengajar_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_mapels', function (Blueprint $table) {
            $table->dropForeign(['jurnal_mengajar_id']);
            $table->dropColumn('jurnal_mengajar_id');
        });
    }
};