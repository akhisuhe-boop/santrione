<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perizinans', function (Blueprint $table) {
            // hapus kolom lama
            $table->dropColumn('tanggal_selesai');
        });

        Schema::table('perizinans', function (Blueprint $table) {
            // buat ulang dengan datetime
            $table->dateTime('tanggal_selesai')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('perizinans', function (Blueprint $table) {
            $table->dropColumn('tanggal_selesai');
        });

        Schema::table('perizinans', function (Blueprint $table) {
            $table->date('tanggal_selesai')->nullable();
        });
    }
};