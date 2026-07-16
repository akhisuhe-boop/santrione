<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected array $tables = [
        'pelanggarans',
        'prestasis',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('lembaga_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        // Backfill: sama seperti migration Fase 1B sebelumnya, data lama
        // (master jenis pelanggaran/prestasi yang sudah ada) diarahkan ke
        // lembaga pertama yang ada. WAJIB diverifikasi manual kalau
        // datanya sebenarnya dipakai lebih dari 1 lembaga.
        $lembagaPertamaId = DB::table('lembagas')->orderBy('id')->value('id');

        if ($lembagaPertamaId) {
            foreach ($this->tables as $table) {
                DB::table($table)->whereNull('lembaga_id')->update([
                    'lembaga_id' => $lembagaPertamaId,
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('lembaga_id');
            });
        }
    }
};
