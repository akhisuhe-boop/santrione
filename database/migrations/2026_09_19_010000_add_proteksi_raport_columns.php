<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | PROTEKSI RAPORT DI TINGKAT LEMBAGA (DIATUR ADMIN/KEUANGAN)
        |--------------------------------------------------------------------------
        | Kebijakan "harus lunas dulu baru bisa lihat raport" berbeda-beda per
        | sekolah, jadi ini dibuat bisa diatur, bukan aturan tetap dari sistem.
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('lembagas', 'proteksi_raport_aktif')) {

            Schema::table('lembagas', function (Blueprint $table) {

                $table->boolean('proteksi_raport_aktif')
                    ->default(false)
                    ->after('kepala_sekolah');

                // tahun_ajaran_aktif = cukup tagihan tahun ajaran berjalan lunas
                // semua_tagihan      = semua tagihan siswa sepanjang waktu lunas
                $table->enum('proteksi_raport_cakupan', [
                    'tahun_ajaran_aktif',
                    'semua_tagihan',
                ])
                    ->default('tahun_ajaran_aktif')
                    ->after('proteksi_raport_aktif');

                // null = semua jenis tagihan wajib lunas
                // diisi = cuma jenis tagihan ini yang dicek (mis. SPP saja)
                $table->foreignId('proteksi_raport_jenis_tagihan_id')
                    ->nullable()
                    ->after('proteksi_raport_cakupan')
                    ->constrained('jenis_tagihans')
                    ->nullOnDelete();

            });
        }

        /*
        |--------------------------------------------------------------------------
        | OVERRIDE PER SISWA
        |--------------------------------------------------------------------------
        | null  = ikut aturan sekolah di atas (default)
        | true  = selalu diizinkan lihat raport walau ada tagihan belum lunas
        | false = selalu diblokir walau tagihannya lunas semua
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('siswas', 'izin_lihat_raport')) {

            Schema::table('siswas', function (Blueprint $table) {

                $table->boolean('izin_lihat_raport')
                    ->nullable()
                    ->default(null);

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('lembagas', 'proteksi_raport_aktif')) {

            Schema::table('lembagas', function (Blueprint $table) {

                $table->dropConstrainedForeignId('proteksi_raport_jenis_tagihan_id');
                $table->dropColumn([
                    'proteksi_raport_aktif',
                    'proteksi_raport_cakupan',
                ]);

            });
        }

        if (Schema::hasColumn('siswas', 'izin_lihat_raport')) {

            Schema::table('siswas', function (Blueprint $table) {
                $table->dropColumn('izin_lihat_raport');
            });
        }
    }
};
