<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pola OPT-OUT: TIDAK ADA baris untuk suatu (lembaga_id, key) ->
     * dianggap AKTIF (default, sama seperti perilaku sekarang, semua
     * notifikasi otomatis jalan). Baris cuma dibuat kalau sekolah
     * EKSPLISIT MATIKAN salah satu jenis notifikasi -- jadi tidak
     * perlu seed ribuan baris (jumlah lembaga x 22 jenis) untuk
     * semua kombinasi, cukup catat pengecualian saja.
     */
    public function up(): void
    {
        Schema::create('notification_type_toggles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembagas')->cascadeOnDelete();
            $table->string('key'); // cocok dengan App\Support\NotificationType
            $table->boolean('is_active')->default(true); // baris dibuat begitu admin sentuh toggle-nya (baik nyalakan atau matikan) -- default true di sini murni jaga-jaga, nilai sebenarnya selalu diisi eksplisit saat baris dibuat
            $table->timestamps();

            $table->unique(['lembaga_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_type_toggles');
    }
};
