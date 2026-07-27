<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sama seperti kasus Kas kemarin: pegawai level pesantren (bukan
     * milik 1 lembaga spesifik, mis. pengurus yayasan) butuh cara
     * untuk TIDAK terikat ke 1 lembaga tertentu. Tapi begitu
     * lembaga_id boleh kosong, tenant-scoping (yang sebelumnya
     * bergantung whereHas('lembagas')) akan bikin pegawai itu tidak
     * kelihatan oleh yayasan manapun. Makanya sekalian ditambah
     * yayasan_id LANGSUNG di tabel pegawais sebagai sumber kebenaran
     * scoping yang baru.
     */
    public function up(): void
    {
        Schema::table('pegawai_lembaga', function (Blueprint $table) {
            $table->foreignId('lembaga_id')->nullable()->change();
        });

        Schema::table('pegawais', function (Blueprint $table) {
            $table->foreignId('yayasan_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        // Backfill data lama lewat penugasan lembaga yang sudah ada.
        DB::statement('
            UPDATE pegawais
            INNER JOIN pegawai_lembaga ON pegawai_lembaga.pegawai_id = pegawais.id
            INNER JOIN lembagas ON lembagas.id = pegawai_lembaga.lembaga_id
            SET pegawais.yayasan_id = lembagas.yayasan_id
            WHERE pegawais.yayasan_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropConstrainedForeignId('yayasan_id');
        });

        Schema::table('pegawai_lembaga', function (Blueprint $table) {
            $table->foreignId('lembaga_id')->nullable(false)->change();
        });
    }
};
