<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->string('hero_video_url')->nullable()->after('hero_mockup_gambar');
            $table->string('meta_pixel_id')->nullable()->after('nomor_akta');
            $table->string('tiktok_pixel_id')->nullable()->after('meta_pixel_id');
            $table->string('google_ads_id')->nullable()->after('tiktok_pixel_id');
        });
    }

    public function down(): void
    {
        Schema::table('landing_settings', function (Blueprint $table) {
            $table->dropColumn(['hero_video_url', 'meta_pixel_id', 'tiktok_pixel_id', 'google_ads_id']);
        });
    }
};
