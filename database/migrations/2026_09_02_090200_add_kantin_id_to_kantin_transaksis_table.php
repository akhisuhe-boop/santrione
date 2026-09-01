<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kantin_transaksis', function (Blueprint $table) {
            $table->foreignId('kantin_id')
                ->nullable()
                ->after('lembaga_id')
                ->constrained('kantins')
                ->nullOnDelete();
        });

        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        // Backfill best-effort: kantin_id transaksi lama diambil dari
        // kantin_id produk pertama yang dibeli di transaksi itu (semua
        // produk dalam 1 transaksi pasti berasal dari kantin yang sama,
        // karena scan produk di kasir sudah dibatasi ke 1 kantin aktif).
        DB::statement('
            UPDATE kantin_transaksis kt
            INNER JOIN (
                SELECT kti.kantin_transaksi_id, MIN(kp.kantin_id) AS kantin_id
                FROM kantin_transaksi_items kti
                INNER JOIN kantin_produks kp ON kp.id = kti.kantin_produk_id
                WHERE kp.kantin_id IS NOT NULL
                GROUP BY kti.kantin_transaksi_id
            ) sub ON sub.kantin_transaksi_id = kt.id
            SET kt.kantin_id = sub.kantin_id
            WHERE kt.kantin_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('kantin_transaksis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kantin_id');
        });
    }
};
