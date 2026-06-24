<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->string('rekening_tujuan')->nullable()->change();
            $table->string('no_hp')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->string('rekening_tujuan')->nullable(false)->change();
            $table->string('no_hp')->nullable(false)->change();
        });
    }
};