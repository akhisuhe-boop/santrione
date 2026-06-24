<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {

            $table->foreignId('ppdb_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('ppdbs')
                ->nullOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {

            $table->dropConstrainedForeignId('ppdb_id');

        });
    }
};