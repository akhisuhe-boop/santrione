<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected array $tables = [
        'rekenings',
        'kartu_templates',
        'whatsapp_settings',
        'announcements',
        'template_kegiatan',
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

        // Backfill data lama: karena sampai migration ini dibuat baru ada
        // 1 lembaga produksi asli (SDIT TCM / SMPIT TCM di bawah Tunas
        // Cendekia Madani), data existing di 5 tabel ini diarahkan ke
        // lembaga pertama yang ada. WAJIB diverifikasi manual kalau
        // ternyata datanya campuran lebih dari 1 lembaga sebelum ini.
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
