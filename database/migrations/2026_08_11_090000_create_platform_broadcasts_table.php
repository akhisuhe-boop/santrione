<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat broadcast/notifikasi yang dikirim platform admin ke
     * Yayasan (update info, pengumuman produk/layanan, dll) --
     * TERPISAH dari notifikasi tagihan otomatis (yang sudah jalan
     * lewat NotificationService::sendTagihanSubscription, tidak
     * tercatat di sini karena sifatnya rutin/otomatis, bukan aksi
     * sadar platform admin).
     *
     * Sengaja simpan target_filter sebagai snapshot JSON (bukan cuma
     * relasi), supaya riwayat tetap bisa dibaca persis kondisi saat
     * dikirim walau nanti ada Yayasan baru/berubah status.
     */
    public function up(): void
    {
        Schema::create('platform_broadcasts', function (Blueprint $table) {
            $table->id();

            $table->string('judul');
            $table->text('pesan');

            $table->json('target_filter'); // {"tipe": "semua"} | {"tipe": "status", "status": ["active"]} | {"tipe": "manual", "yayasan_ids": [1,2]}

            $table->unsignedInteger('jumlah_penerima')->default(0);
            $table->unsignedInteger('jumlah_berhasil')->default(0);

            $table->enum('status', ['draft', 'terkirim', 'gagal_sebagian'])->default('draft');

            $table->foreignId('dikirim_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dikirim_pada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_broadcasts');
    }
};
