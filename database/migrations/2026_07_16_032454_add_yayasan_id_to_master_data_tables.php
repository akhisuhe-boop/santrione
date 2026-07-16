<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected array $tables = [
        'mata_pelajarans',
        'tahun_ajarans',
        'jenis_tagihans',
        'kategori_kas',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('yayasan_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        // Backfill data lama ke yayasan dari lembaga pertama yang ada.
        // WAJIB diverifikasi manual di production kalau datanya sebenarnya
        // dipakai lebih dari 1 yayasan.
        $yayasanPertamaId = DB::table('yayasans')->orderBy('id')->value('id');

        if ($yayasanPertamaId) {
            foreach ($this->tables as $table) {
                DB::table($table)->whereNull('yayasan_id')->update([
                    'yayasan_id' => $yayasanPertamaId,
                ]);
            }
        }

        // 'kode' di jenis_tagihans & kategori_kas sebelumnya unique secara
        // GLOBAL (1 tabel se-database) — sekarang harus unique PER YAYASAN,
        // supaya 2 yayasan berbeda tetap boleh pakai kode yang sama
        // (mis. sama-sama "masuk_spp").
        Schema::table('jenis_tagihans', function (Blueprint $blueprint) {
            $blueprint->dropUnique('jenis_tagihans_kode_unique');
            $blueprint->unique(['yayasan_id', 'kode']);
        });

        Schema::table('kategori_kas', function (Blueprint $blueprint) {
            $blueprint->dropUnique('kategori_kas_kode_unique');
            $blueprint->unique(['yayasan_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::table('jenis_tagihans', function (Blueprint $blueprint) {
            $blueprint->dropUnique(['yayasan_id', 'kode']);
            $blueprint->unique('kode');
        });

        Schema::table('kategori_kas', function (Blueprint $blueprint) {
            $blueprint->dropUnique(['yayasan_id', 'kode']);
            $blueprint->unique('kode');
        });

        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('yayasan_id');
            });
        }
    }
};
