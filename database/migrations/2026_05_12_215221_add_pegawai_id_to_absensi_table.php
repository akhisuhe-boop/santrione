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
        Schema::table('absensi', function (Blueprint $table) {

            $table->foreignId('pegawai_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('pegawais')
                ->nullOnDelete();

            $table->enum('tipe', ['siswa', 'guru'])
                ->nullable()
                ->after('pegawai_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {

            $table->dropForeign(['pegawai_id']);

            $table->dropColumn([
                'pegawai_id',
                'tipe'
            ]);

        });
    }
};