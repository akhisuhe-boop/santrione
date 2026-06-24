<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setting_nominal_tagihans', function (Blueprint $table) {
            $table->foreignId('siswa_id')
                ->nullable()
                ->constrained('siswas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('setting_nominal_tagihans', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
            $table->dropColumn('siswa_id');
        });
    }
};
