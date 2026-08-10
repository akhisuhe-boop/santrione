<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Modul apa saja yang AKTIF di tiap Lembaga — inilah yang bikin
     * skema "à la carte per lembaga" di dokumen bisa jalan (mis. SDIT
     * pakai Tahfidz, SMA tidak, walau satu Yayasan yang sama).
     *
     * unique(lembaga_id, module_price_id): satu modul cuma boleh
     * "aktif" sekali per lembaga — reaktivasi modul yang pernah
     * dimatikan UPDATE baris yang sama, bukan bikin baris baru,
     * supaya App\Services\TenantBillingCalculator tidak pernah
     * menjumlah modul yang sama dua kali secara tidak sengaja.
     */
    public function up(): void
    {
        Schema::create('lembaga_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lembaga_id')
                ->constrained('lembagas')
                ->cascadeOnDelete();

            $table->foreignId('module_price_id')
                ->constrained('module_prices')
                ->cascadeOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamp('aktif_sejak')->nullable();
            $table->timestamp('nonaktif_sejak')->nullable();

            $table->timestamps();

            $table->unique(['lembaga_id', 'module_price_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembaga_modules');
    }
};
