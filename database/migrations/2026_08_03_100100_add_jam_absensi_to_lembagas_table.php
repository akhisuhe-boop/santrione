<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->time('jam_masuk_siswa')->nullable();
            $table->time('jam_pulang_siswa')->nullable();
            $table->time('jam_masuk_guru')->nullable();
            $table->time('jam_pulang_guru')->nullable();
            $table->unsignedInteger('toleransi_telat_menit')->default(15);
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn([
                'jam_masuk_siswa',
                'jam_pulang_siswa',
                'jam_masuk_guru',
                'jam_pulang_guru',
                'toleransi_telat_menit',
            ]);
        });
    }
};
