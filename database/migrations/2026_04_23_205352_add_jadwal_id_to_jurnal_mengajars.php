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
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->foreignId('jadwal_id')
            ->nullable()
            ->constrained('jadwal_pelajarans')
            ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->dropForeign(['jadwal_id']); // 🔥 penting
            $table->dropColumn('jadwal_id');
        });
    }
};
