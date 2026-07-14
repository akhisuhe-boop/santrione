<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Nullable dulu supaya aman untuk data user yang sudah ada.
     * Setelah data dimigrasikan manual (isi yayasan_id untuk semua user
     * lama), boleh diubah jadi NOT NULL di migration terpisah.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('yayasan_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();

            // flag khusus untuk admin platform (kamu) — user dengan flag ini
            // TIDAK kena global scope tenant, supaya tetap bisa lihat semua
            // data lintas yayasan untuk keperluan support/monitoring.
            $table->boolean('is_platform_admin')->default(false)->after('yayasan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('yayasan_id');
            $table->dropColumn('is_platform_admin');
        });
    }
};
