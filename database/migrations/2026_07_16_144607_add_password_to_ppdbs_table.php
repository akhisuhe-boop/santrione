<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bug lama (bukan terkait tenancy): PpdbResource sudah lama otomatis
     * generate password (hash dari NISN) untuk login portal pendaftar,
     * tapi kolomnya tidak pernah benar-benar dibuat di migration awal.
     * Sama seperti kasus 'kompetensi' yang hilang di mata_pelajarans.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('ppdbs', 'password')) {
            Schema::table('ppdbs', function (Blueprint $table) {
                $table->string('password')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ppdbs', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
