<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kantin_transaksis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lembaga_id')
                ->constrained('lembagas')
                ->cascadeOnDelete();

            $table->string('kode')->unique();

            // Nullable: mendukung transaksi tanpa siswa (mis. guru/tamu
            // beli tunai), meski penggunaan utamanya untuk siswa.
            $table->foreignId('siswa_id')
                ->nullable()
                ->constrained('siswas')
                ->nullOnDelete();

            $table->foreignId('wallet_id')
                ->nullable()
                ->constrained('wallets')
                ->nullOnDelete();

            // wallet | tunai
            $table->string('metode');

            $table->bigInteger('total');

            $table->foreignId('kasir_id')
                ->nullable()
                ->constrained('pegawais')
                ->nullOnDelete();

            $table->foreignId('kas_id')
                ->nullable()
                ->constrained('kas')
                ->nullOnDelete();

            $table->timestamp('tanggal')->useCurrent();

            $table->timestamps();
        });

        Schema::create('kantin_transaksi_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kantin_transaksi_id')
                ->constrained('kantin_transaksis')
                ->cascadeOnDelete();

            $table->foreignId('kantin_produk_id')
                ->nullable()
                ->constrained('kantin_produks')
                ->nullOnDelete();

            // Snapshot nama & harga saat transaksi — supaya laporan lama
            // tidak berubah kalau produk/harga diedit belakangan.
            $table->string('nama_produk');
            $table->bigInteger('harga_satuan');
            $table->unsignedInteger('qty');
            $table->bigInteger('subtotal');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kantin_transaksi_items');
        Schema::dropIfExists('kantin_transaksis');
    }
};
