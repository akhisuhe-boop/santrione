<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
    
            $table->foreignId('bendahara_id')
                ->nullable()
                ->after('kepala_sekolah')
                ->constrained('pegawais')
                ->nullOnDelete();
    
            $table->string('npsn', 30)
                ->nullable()
                ->after('bendahara_id');
    
            $table->string('nss', 30)
                ->nullable()
                ->after('npsn');
    
            $table->string('printer_kwitansi', 20)
                ->default('thermal80')
                ->after('nss');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
    
            $table->dropForeign(['bendahara_id']);
    
            $table->dropColumn([
                'bendahara_id',
                'npsn',
                'nss',
                'printer_kwitansi',
            ]);
        });
    }
};