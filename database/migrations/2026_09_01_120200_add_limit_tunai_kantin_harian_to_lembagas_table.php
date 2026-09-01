<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Batas jumlah transaksi TUNAI (pengunjung, tanpa kartu) per hari di
     * kasir kantin 1 lembaga. Null = tidak dibatasi. Begitu limit
     * tercapai, tombol "Transaksi Pengunjung (Tunai)" di kasir terkunci
     * sampai besok -- supaya kasir tidak bisa terus-menerus pakai tunai
     * sebagai jalan pintas (harusnya cuma untuk pengunjung asli, bukan
     * siswa/guru yang malas scan kartu).
     */
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->unsignedInteger('limit_tunai_kantin_harian')
                ->nullable()
                ->after('printer_kwitansi');
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn('limit_tunai_kantin_harian');
        });
    }
};
