<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->foreignId('mata_pelajaran_id')
                ->nullable() // biar tidak error saat data lama belum diisi
                ->after('mata_pelajaran')
                ->constrained('mata_pelajarans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->dropForeign(['mata_pelajaran_id']); // hapus foreign key dulu
            $table->dropColumn('mata_pelajaran_id');   // baru hapus kolom
        });
    }
};