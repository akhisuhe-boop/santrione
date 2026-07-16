<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bug lama (bukan terkait tenancy): model MataPelajaran dan form-nya
     * sudah lama punya field 'kompetensi', tapi kolomnya tidak pernah
     * benar-benar dibuat di migration awal. Baru ketahuan sekarang karena
     * memang belum pernah ada yang isi field itu sebelumnya.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('mata_pelajarans', 'kompetensi')) {
            Schema::table('mata_pelajarans', function (Blueprint $table) {
                $table->text('kompetensi')->nullable()->after('nama');
            });
        }
    }

    public function down(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropColumn('kompetensi');
        });
    }
};
