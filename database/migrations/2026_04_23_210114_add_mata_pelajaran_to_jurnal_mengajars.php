<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->string('mata_pelajaran')->nullable()->after('kelas_id');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->dropColumn('mata_pelajaran');
        });
    }
};