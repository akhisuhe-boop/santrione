<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kantin_produks', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('kantin_produks', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
