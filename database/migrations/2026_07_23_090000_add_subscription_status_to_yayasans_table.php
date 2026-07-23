<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status langganan yayasan (Fase 5 — SaaS billing):
     *
     *  - trial     : baru daftar, masih dalam masa coba gratis
     *  - active    : sedang berlangganan aktif (sudah bayar)
     *  - suspended : trial habis / langganan telat bayar lewat masa
     *                tenggang — akses panel DIKUNCI, data TIDAK dihapus
     *  - cancelled : yayasan berhenti berlangganan (arsip)
     */
    public function up(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {
            $table->string('status')->default('trial')->after('slug');
            $table->timestamp('trial_ends_at')->nullable()->after('status');
        });

        // PENTING: yayasan yang SUDAH ADA sebelum fitur billing ini
        // wajib "digrandfather" jadi 'active' (akses tetap terbuka
        // seperti biasa, tanpa perlu langganan) — supaya migration ini
        // TIDAK mengunci siapapun yang sudah pakai aplikasi. Status
        // 'trial' hanya berlaku untuk yayasan yang baru daftar SETELAH
        // fitur ini ada (lihat Yayasan::booted()).
        DB::table('yayasans')->update(['status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {
            $table->dropColumn(['status', 'trial_ends_at']);
        });
    }
};
