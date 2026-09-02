<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sistem wallet guru/pegawai (ditambahkan migration
     * add_pegawai_id_to_wallets_table) DIBATALKAN dulu -- guru tetap
     * bisa belanja di kasir kantin pakai kartunya sendiri (niy/rfid/
     * qr_code), tapi SELALU bayar TUNAI (bukan potong wallet), dan
     * tunduk pada limit transaksi tunai harian yang sama dengan
     * pengunjung -- diatur platform admin lewat
     * LembagaKantinPengaturanResource (lihat KasirKantin::checkout()).
     *
     * Hapus dulu baris wallet yang sudah kepalang dibuat/backfill untuk
     * pegawai (siswa_id null, pegawai_id terisi) sebelum kolomnya
     * di-drop, supaya tidak ada baris "yatim" tersisa di tabel.
     */
    public function up(): void
    {
        DB::table('wallets')->whereNull('siswa_id')->whereNotNull('pegawai_id')->delete();

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pegawai_id');
        });

        // Kembalikan siswa_id jadi NOT NULL lagi -- semua wallet yang
        // tersisa sekarang pasti milik siswa.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE wallets MODIFY COLUMN siswa_id BIGINT UNSIGNED NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->foreignId('pegawai_id')
                ->nullable()
                ->after('siswa_id')
                ->constrained('pegawais')
                ->cascadeOnDelete();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE wallets MODIFY COLUMN siswa_id BIGINT UNSIGNED NULL');
        }
    }
};
