<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            // Snapshot jumlah siswa AKTIF yang dikunci tiap awal bulan
            // oleh job billing bulanan (lihat GenerateTenantInvoices) —
            // sengaja dikunci, bukan dihitung live saat invoice dibuat,
            // supaya siswa masuk/keluar di tengah bulan tidak mengubah
            // tagihan yang sudah terbit (hindari sengketa dengan sekolah).
            $table->unsignedInteger('jumlah_siswa_billing')->nullable()->after('max_jp_guru_per_minggu');
            $table->timestamp('siswa_billing_snapshot_at')->nullable()->after('jumlah_siswa_billing');

            // Urutan Lembaga ke berapa (1, 2, 3, ...) dalam Yayasannya
            // untuk keperluan diskon volume biaya Akses Platform
            // (Lembaga 1 = normal, 2-3 = diskon 20%, 4+ = diskon 35%).
            // Nullable: kalau kosong, TenantBillingCalculator fallback
            // urut berdasarkan Lembaga.id (urutan pendaftaran) — kolom
            // ini ada supaya Platform Admin bisa override manual kalau
            // suatu saat urutannya perlu diubah tanpa mengacak id.
            $table->unsignedInteger('urutan_billing')->nullable()->after('siswa_billing_snapshot_at');
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn(['jumlah_siswa_billing', 'siswa_billing_snapshot_at', 'urutan_billing']);
        });
    }
};
