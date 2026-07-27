<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sebelum ini, sistem selalu tahu "kas ini punya yayasan siapa"
     * LEWAT lembaga_id (lembaga -> yayasan). Begitu lembaga_id
     * dibolehkan kosong (buat transaksi level yayasan/pesantren,
     * bukan 1 lembaga spesifik), baris itu jadi tidak kelihatan sama
     * sekali oleh scoping tenant manapun — karena tidak ada lagi
     * "jalan" ke yayasan-nya.
     *
     * Fix: kolom yayasan_id LANGSUNG di tabel kas, independen dari
     * lembaga_id. Selalu diisi otomatis (lihat Kas::booted()),
     * dipakai sebagai satu-satunya sumber kebenaran untuk tenant
     * scoping ke depannya — tidak lagi bergantung ada/tidaknya
     * lembaga_id.
     */
    public function up(): void
    {
        Schema::table('kas', function (Blueprint $table) {
            $table->foreignId('yayasan_id')
                ->nullable()
                ->after('lembaga_id')
                ->constrained()
                ->nullOnDelete();
        });

        // Backfill data lama: ambil yayasan_id dari lembaga yang sudah
        // ada di tiap baris (hampir semua baris lama pasti punya
        // lembaga_id, karena fitur "kosongkan lembaga" ini baru).
        DB::statement('
            UPDATE kas
            INNER JOIN lembagas ON lembagas.id = kas.lembaga_id
            SET kas.yayasan_id = lembagas.yayasan_id
            WHERE kas.lembaga_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('kas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('yayasan_id');
        });
    }
};
