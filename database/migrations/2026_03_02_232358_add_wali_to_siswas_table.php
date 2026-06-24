<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {

            // Data Wali
            $table->string('nik_wali')->nullable();
            $table->string('nama_wali')->nullable();
            $table->string('status_wali')->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('pendidikan_wali')->nullable();
            $table->string('penghasilan_wali')->nullable();
            $table->string('wa_wali')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn([
                'nik_wali','nama_wali','status_wali',
                'pekerjaan_wali','pendidikan_wali',
                'penghasilan_wali','wa_wali'
            ]);
        });
    }
};