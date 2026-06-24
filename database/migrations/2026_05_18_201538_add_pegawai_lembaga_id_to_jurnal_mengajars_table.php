<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {

            $table->foreignId('pegawai_lembaga_id')
                ->nullable()
                ->after('pegawai_id')
                ->constrained('pegawai_lembaga')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {

            $table->dropConstrainedForeignId('pegawai_lembaga_id');

        });
    }
};