<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai_lembaga', function (Blueprint $table) {

            $table->enum('metode_penggajian', [
                'tetap',
                'per_jp'
            ])->default('tetap');

            $table->bigInteger('nominal_tetap')
                ->default(0);

            $table->bigInteger('tarif_per_jp')
                ->default(0);

        });
    }

    public function down(): void
    {
        Schema::table('pegawai_lembaga', function (Blueprint $table) {

            $table->dropColumn([
                'metode_penggajian',
                'nominal_tetap',
                'tarif_per_jp'
            ]);

        });
    }
};