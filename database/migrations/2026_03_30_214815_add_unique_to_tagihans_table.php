<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->unique([
                'siswa_id',
                'jenis_tagihan_id',
                'tahun_ajaran_id',
                'bulan'
            ], 'tagihan_unique_key');
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropUnique('tagihan_unique_key');
        });
    }
};
