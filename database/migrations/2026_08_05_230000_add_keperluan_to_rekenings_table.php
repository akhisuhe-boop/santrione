<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            $table->string('keperluan')->nullable()->after('tipe');
        });
    }

    public function down(): void
    {
        Schema::table('rekenings', function (Blueprint $table) {
            $table->dropColumn('keperluan');
        });
    }
};
