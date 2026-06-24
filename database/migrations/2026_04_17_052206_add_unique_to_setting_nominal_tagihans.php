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
        Schema::table('setting_nominal_tagihans', function (Blueprint $table) {

            $table->unique([
                'jenis_tagihan_id',
                'lembaga_id',
                'kelas_id',
                'siswa_id',
                'bulan',
                'tahun_ajaran_id'
            ], 'unique_setting_tagihan');

        });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('setting_nominal_tagihans', function (Blueprint $table) {

            $table->dropUnique('unique_setting_tagihan');

        });
    }
};
