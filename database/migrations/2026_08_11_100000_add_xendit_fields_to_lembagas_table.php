<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mendukung 2 payment gateway berdampingan (Duitku yang sudah
     * jalan sekarang, Xendit for Platforms sebagai opsi baru) --
     * SENGAJA default 'duitku' supaya SEMUA Lembaga existing tidak
     * berubah perilaku apapun setelah migration ini jalan. Hanya
     * Lembaga yang eksplisit di-set 'xendit' (lewat proses onboarding
     * sub-account) yang pindah jalur.
     */
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->enum('payment_gateway', ['duitku', 'xendit'])->default('duitku')->after('urutan_billing');

            // ID sub-account/account-holder Xendit untuk Lembaga ini --
            // null selama belum pernah didaftarkan (belum bisa terima
            // pembayaran via Xendit sampai ini terisi).
            $table->string('xendit_account_holder_id')->nullable()->after('payment_gateway');
            $table->enum('xendit_status', ['belum_daftar', 'menunggu_verifikasi', 'aktif', 'ditolak'])
                ->default('belum_daftar')->after('xendit_account_holder_id');
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn(['payment_gateway', 'xendit_account_holder_id', 'xendit_status']);
        });
    }
};
