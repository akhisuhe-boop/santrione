<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 1) Menambahkan DOKU sebagai payment_gateway yang sah untuk
     *    Lembaga (dampingan Xendit -- Duitku sengaja TIDAK
     *    dihapus dari enum supaya data lama tidak rusak, tapi
     *    tidak lagi dipakai kode manapun setelah migration ini).
     * 2) Menambah kolom doku_sub_account_id + doku_status, sejajar
     *    dengan xendit_account_holder_id + xendit_status yang sudah ada.
     * 3) FIX BUG: kolom `pembayarans.metode` adalah ENUM
     *    ['admin','ewallet','transfer','gateway'] -- tapi kode lama
     *    (DuitkuController, XenditService, WaliDashboardController::duitku())
     *    menulis 'duitku'/'xendit' langsung ke kolom itu, nilai yang
     *    TIDAK ADA di enum. Menambahkan kolom `gateway` terpisah
     *    (nullable, bebas isi 'doku'/'xendit') untuk menyimpan nama
     *    provider sebenarnya, sementara `metode` tetap diisi 'gateway'
     *    sesuai enum yang berlaku.
     *
     * Statement ALTER ... MODIFY COLUMN ... ENUM(...) khusus MySQL,
     * di-skip saat testing (sqlite in-memory) -- SQLite tidak punya
     * ENUM/MODIFY COLUMN, dan kolomnya sudah longgar tanpa perlu
     * diperlebar.
     */
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->string('doku_sub_account_id')->nullable()->after('xendit_status');
            $table->enum('doku_status', ['belum_daftar', 'menunggu_verifikasi', 'aktif', 'ditolak'])
                ->default('belum_daftar')->after('doku_sub_account_id');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE lembagas MODIFY COLUMN payment_gateway ENUM('duitku','xendit','doku') NOT NULL DEFAULT 'duitku'");
        }

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('metode');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('reference_id');
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn(['doku_sub_account_id', 'doku_status']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE lembagas MODIFY COLUMN payment_gateway ENUM('duitku','xendit') NOT NULL DEFAULT 'duitku'");
        }

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropColumn('gateway');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('gateway');
        });
    }
};
