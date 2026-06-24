<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('tagihan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('siswa_id')->nullable()->constrained()->cascadeOnDelete();

            // Kode pembayaran
            $table->string('kode')->unique(); // PAY-2026-0001

            // Nominal bayar
            $table->unsignedBigInteger('nominal');

            // Metode pembayaran
            $table->enum('metode', [
                'admin',
                'ewallet',
                'transfer',
                'gateway'
            ]);

            // Status transaksi
            $table->enum('status', [
                'pending',
                'sukses',
                'gagal'
            ])->default('pending');

            // Bukti transfer (optional)
            $table->string('bukti_transfer')->nullable();

            // Waktu bayar
            $table->timestamp('tanggal_bayar')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Index biar cepat
            $table->index(['tagihan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
