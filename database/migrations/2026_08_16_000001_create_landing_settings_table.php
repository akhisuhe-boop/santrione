<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name')->default('Qinara Apps');
            $table->string('headline_baris1')->nullable();
            $table->string('headline_baris2')->nullable();
            $table->text('subheadline')->nullable();
            $table->string('badge_text')->nullable();

            // Kontak & sosial media
            $table->string('whatsapp_number')->nullable();
            $table->text('whatsapp_pesan_default')->nullable();
            $table->string('email_kontak')->nullable();
            $table->string('alamat')->nullable();
            $table->string('ig_url')->nullable();
            $table->string('fb_url')->nullable();
            $table->string('yt_url')->nullable();
            $table->string('x_url')->nullable();

            // Kartu mockup dashboard di hero
            $table->string('hero_mockup_gambar')->nullable();
            $table->string('hero_kpi_keuangan')->nullable();
            $table->string('hero_kpi_keuangan_growth')->nullable();
            $table->decimal('hero_kpi_kehadiran_persen', 5, 1)->nullable();

            // Statistik ringkas
            $table->string('social_proof_text')->nullable();
            $table->string('stat_efisiensi')->nullable();
            $table->string('stat_modul')->nullable();
            $table->string('stat_akses')->nullable();
            $table->string('stat_digitalisasi')->nullable();

            $table->string('footer_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_settings');
    }
};
