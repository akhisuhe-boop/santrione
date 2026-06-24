<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('kurikulums', function (Blueprint $table) {
        $table->unique(['kelas_id', 'mata_pelajaran_id']);
    });
}

public function down(): void
{
    Schema::table('kurikulums', function (Blueprint $table) {
        $table->dropUnique(['kelas_id', 'mata_pelajaran_id']);
    });
}
};
