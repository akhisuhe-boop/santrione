<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill: setiap Lembaga yang sudah punya produk kantin dibuatkan
     * 1 Kantin default ("Kantin <nama lembaga>"), limit tunai yang sudah
     * diatur platform admin di lembaga ikut dipindah supaya tidak hilang.
     * Semua produk existing lembaga itu di-assign ke Kantin barunya.
     * Ini cuma TITIK AWAL -- tenant bebas nambah kantin lain sesudahnya
     * yang tidak terikat lembaga manapun.
     */
    public function up(): void
    {
        Schema::table('kantin_produks', function (Blueprint $table) {
            $table->foreignId('kantin_id')
                ->nullable()
                ->after('lembaga_id')
                ->constrained('kantins')
                ->cascadeOnDelete();

            // lembaga_id tidak lagi wajib -- kantin_id yang jadi acuan utama.
            $table->unsignedBigInteger('lembaga_id')->nullable()->change();
        });

        if (DB::connection()->getDriverName() !== 'mysql') {
            return; // backfill raw query di bawah ini MySQL-spesifik, aman di-skip saat testing sqlite
        }

        $now = now();

        $lembagas = DB::table('kantin_produks')
            ->whereNotNull('lembaga_id')
            ->select('lembaga_id')
            ->distinct()
            ->pluck('lembaga_id');

        foreach ($lembagas as $lembagaId) {

            $lembaga = DB::table('lembagas')->where('id', $lembagaId)->first();

            if (! $lembaga) {
                continue;
            }

            $kantinId = DB::table('kantins')->insertGetId([
                'yayasan_id' => $lembaga->yayasan_id,
                'lembaga_id' => $lembagaId,
                'nama' => 'Kantin ' . $lembaga->nama,
                'is_active' => true,
                'limit_tunai_kantin_harian' => $lembaga->limit_tunai_kantin_harian ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('kantin_produks')
                ->where('lembaga_id', $lembagaId)
                ->update(['kantin_id' => $kantinId]);
        }
    }

    public function down(): void
    {
        Schema::table('kantin_produks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kantin_id');
        });
    }
};
