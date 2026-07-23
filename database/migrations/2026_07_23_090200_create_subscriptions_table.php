<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('yayasan_id')
                ->constrained('yayasans')
                ->cascadeOnDelete();

            $table->foreignId('subscription_plan_id')
                ->constrained('subscription_plans');

            // pending   : nunggu pembayaran (manual transfer belum
            //             diverifikasi, atau Midtrans belum settlement)
            // active    : sedang berjalan
            // expired   : masa aktifnya sudah lewat, belum diperpanjang
            // cancelled : dibatalkan
            $table->string('status')->default('pending');

            $table->timestamp('mulai_pada')->nullable();
            $table->timestamp('berakhir_pada')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
