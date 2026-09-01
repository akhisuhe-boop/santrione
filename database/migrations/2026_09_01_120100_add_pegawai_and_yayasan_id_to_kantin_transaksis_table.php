<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mendukung 3 jenis pembeli di kasir kantin: siswa (wallet), guru/
     * pegawai (wallet), dan pengunjung umum (tunai -- tanpa siswa_id,
     * pegawai_id, ATAUPUN lembaga_id sama sekali, karena pengunjung tidak
     * terikat lembaga manapun).
     *
     * lembaga_id dilonggarkan jadi nullable. Begitu lembaga_id boleh
     * kosong, baris itu jadi tidak kelihatan lagi lewat tenant scope
     * whereHas('lembaga', ...) milik KantinTransaksi -- persis masalah
     * yang sama pernah terjadi di tabel kas (lihat migration
     * add_yayasan_id_to_kas_table). Fix-nya sama: yayasan_id ditambahkan
     * LANGSUNG di tabel ini sebagai sumber kebenaran tenant scoping,
     * independen dari lembaga_id (lihat KantinTransaksi::applyTenantScope
     * & booted()).
     */
    public function up(): void
    {
        Schema::table('kantin_transaksis', function (Blueprint $table) {

            $table->foreignId('pegawai_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('pegawais')
                ->nullOnDelete();

            $table->foreignId('yayasan_id')
                ->nullable()
                ->after('lembaga_id')
                ->constrained('yayasans')
                ->nullOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE kantin_transaksis MODIFY COLUMN lembaga_id BIGINT UNSIGNED NULL');

            // Backfill yayasan_id dari lembaga yang sudah ada di tiap baris lama.
            DB::statement('
                UPDATE kantin_transaksis
                INNER JOIN lembagas ON lembagas.id = kantin_transaksis.lembaga_id
                SET kantin_transaksis.yayasan_id = lembagas.yayasan_id
                WHERE kantin_transaksis.lembaga_id IS NOT NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('kantin_transaksis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pegawai_id');
            $table->dropConstrainedForeignId('yayasan_id');
        });
    }
};
