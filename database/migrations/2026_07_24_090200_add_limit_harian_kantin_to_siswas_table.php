<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // Batas belanja kantin per hari (via wallet). Null = tidak
            // dibatasi. Diatur oleh wali murid lewat portal wali.
            $table->bigInteger('limit_harian_kantin')->nullable()->after('pin');
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('limit_harian_kantin');
        });
    }
};
