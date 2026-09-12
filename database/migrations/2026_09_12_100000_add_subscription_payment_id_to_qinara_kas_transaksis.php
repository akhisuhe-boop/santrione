<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- penghubung ke SubscriptionPayment supaya Kas Masuk
 * bisa otomatis tercatat saat pembayaran client BERHASIL lewat
 * payment gateway (DOKU/Xendit) ATAU verifikasi manual transfer
 * (lihat App\Observers\SubscriptionPaymentObserver).
 *
 * Nullable + UNIQUE: nullable karena transaksi kas manual (input
 * langsung oleh admin) tidak terhubung ke pembayaran mana pun;
 * unique supaya satu SubscriptionPayment TIDAK PERNAH tercatat 2x
 * sebagai Kas Masuk walau observer-nya somehow terpanggil berkali-
 * kali (idempotent di level database, bukan cuma di level kode).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qinara_kas_transaksis', function (Blueprint $table) {
            $table->foreignId('subscription_payment_id')
                ->nullable()
                ->unique()
                ->after('kategori_id')
                ->constrained('subscription_payments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('qinara_kas_transaksis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_payment_id');
        });
    }
};
