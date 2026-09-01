<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Wallet e-kantin sekarang bisa dipunyai SISWA atau PEGAWAI (guru/staf)
     * -- sebelumnya cuma siswa. siswa_id dilonggarkan jadi nullable (tidak
     * lagi NOT NULL) supaya wallet pegawai bisa dibuat tanpa siswa_id, dan
     * pegawai_id baru ditambahkan sebagai pasangannya.
     *
     * App-level (lihat Wallet model + WalletService) yang menjaga setiap
     * wallet selalu punya TEPAT SATU pemilik (siswa_id XOR pegawai_id) --
     * tidak dipaksa lewat DB constraint karena versi MySQL di produksi
     * tidak konsisten dukung CHECK constraint.
     */
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->foreignId('pegawai_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('pegawais')
                ->cascadeOnDelete();
        });

        // Lepas NOT NULL dari siswa_id. Sintaks ALTER ... MODIFY COLUMN ini
        // khusus MySQL (pola yang sama dipakai migration lain di proyek
        // ini) -- di-skip di SQLite (dipakai saat testing) karena SQLite
        // tidak punya ALTER COLUMN semacam ini sama sekali.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE wallets MODIFY COLUMN siswa_id BIGINT UNSIGNED NULL');
        }

        // Backfill: pegawai yang sudah ada sebelum fitur ini belum punya
        // wallet sama sekali -- buatkan wallet saldo 0, konsisten dengan
        // siswa (yang otomatis dapat wallet sejak baris siswa dibuat, lihat
        // Siswa::booted()).
        $now = now();

        DB::table('pegawais')->pluck('id')->chunk(200)->each(function ($chunk) use ($now) {
            DB::table('wallets')->insert(
                $chunk->map(fn ($id) => [
                    'pegawai_id' => $id,
                    'siswa_id' => null,
                    'saldo' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pegawai_id');
        });
    }
};
