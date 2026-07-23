<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subscription_id')
                ->constrained('subscriptions')
                ->cascadeOnDelete();

            $table->bigInteger('jumlah');

            // duitku | midtrans | manual_transfer
            $table->string('metode');

            // pending | berhasil | gagal
            $table->string('status')->default('pending');

            // Khusus manual_transfer: bukti upload + siapa & kapan
            // diverifikasi admin.
            $table->string('bukti_transfer')->nullable();
            $table->foreignId('diverifikasi_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('diverifikasi_pada')->nullable();

            // Khusus payment gateway (Duitku/Midtrans) — nama kolom
            // sengaja generik karena dipakai bareng oleh keduanya.
            $table->string('gateway_order_id')->nullable()->unique();
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_raw_response')->nullable();

            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
