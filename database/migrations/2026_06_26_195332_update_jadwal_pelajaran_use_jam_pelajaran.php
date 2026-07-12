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
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->foreignId('jam_pelajaran_id')
                ->nullable()
                ->after('hari')
                ->constrained('jam_pelajarans')
                ->nullOnDelete();
        });
    }
    
    public function down(): void
    {
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->dropForeign(['jam_pelajaran_id']);
            $table->dropColumn('jam_pelajaran_id');
        });
    }
};
