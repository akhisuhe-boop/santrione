<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kartu_templates', function (Blueprint $table) {
            $table->string('jenis')->default('siswa'); // 🔥 penting
        });
    }

    public function down(): void
    {
        Schema::table('kartu_templates', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};