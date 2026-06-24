<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setting_nominal_tagihans', function (Blueprint $table) {

            if (!Schema::hasColumn('setting_nominal_tagihans', 'lembaga_id')) {
                $table->foreignId('lembaga_id')
                    ->nullable()
                    ->after('jenis_tagihan_id')
                    ->constrained()
                    ->nullOnDelete();
            }

        });
    }

    public function down(): void
    {
        Schema::table('setting_nominal_tagihans', function (Blueprint $table) {

            if (Schema::hasColumn('setting_nominal_tagihans', 'lembaga_id')) {
                $table->dropForeign(['lembaga_id']); // 🔥 penting
                $table->dropColumn('lembaga_id');
            }

        });
    }
};