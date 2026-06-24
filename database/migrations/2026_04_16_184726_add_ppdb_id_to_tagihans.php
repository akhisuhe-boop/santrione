<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->foreignId('ppdb_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropForeign(['ppdb_id']);
            $table->dropColumn('ppdb_id');
        });
    }
};