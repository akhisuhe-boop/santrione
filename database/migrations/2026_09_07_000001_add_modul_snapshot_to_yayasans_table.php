<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simpan "potret" modul mana yang aktif per Lembaga SEBELUM Paket
     * Full diaktifkan -- supaya kalau tenant klik "Kembali Pilih
     * Satu-satu" (batalkanPaketFull), pilihannya bisa dikembalikan
     * persis seperti semula, bukan dibiarkan semua modul tetap aktif.
     *
     * Format JSON: { "<lembaga_id>": [<module_price_id>, ...], ... }
     * Null berarti belum ada snapshot tersimpan (belum pernah
     * mengaktifkan Paket Full, atau sudah pernah di-restore/dihapus).
     */
    public function up(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {
            $table->json('modul_snapshot_sebelum_full')->nullable()->after('promo_pendaftaran_terpakai');
        });
    }

    public function down(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {
            $table->dropColumn('modul_snapshot_sebelum_full');
        });
    }
};
