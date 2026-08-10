<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Subscription lama = harga flat, langsung ambil dari
     * subscription_plan.harga_bulanan (lihat DuitkuSubscriptionService).
     *
     * Skema baru (à la carte modul + siswa + diskon multi-lembaga)
     * butuh nominal yang BEDA tiap bulan tiap Yayasan — computed_amount
     * menyimpan hasil hitungan itu, dan DuitkuSubscriptionService
     * dipatch untuk pakai computed_amount ?? plan->harga_bulanan
     * (jadi subscription lama yang belum pernah dihitung ulang tetap
     * jalan seperti biasa, tidak breaking).
     *
     * computed_breakdown menyimpan rincian penuh (per lembaga, per
     * modul) persis seperti yang ditampilkan di invoice — supaya
     * angka yang ditagih & angka yang ditampilkan ke Yayasan SELALU
     * berasal dari satu sumber yang sama, tidak pernah dihitung ulang
     * manual di tempat lain (itulah akar masalah salah hitung yang
     * kita temukan sebelumnya).
     *
     * periode menandai bulan tagihan ini berlaku (format "2026-08"),
     * dipakai job billing bulanan untuk cek "sudah pernah dibuatkan
     * invoice periode ini belum" sebelum generate yang baru.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->bigInteger('computed_amount')->nullable()->after('subscription_plan_id');
            $table->json('computed_breakdown')->nullable()->after('computed_amount');
            $table->string('periode', 7)->nullable()->after('computed_breakdown');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['computed_amount', 'computed_breakdown', 'periode']);
        });
    }
};
