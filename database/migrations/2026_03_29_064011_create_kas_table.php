<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kas', function (Blueprint $table) {
            $table->id();

            $table->string('kode')->unique();

            $table->enum('tipe', ['masuk', 'keluar']);

            $table->string('kategori');

            // 🔥 langsung include rekening
            $table->foreignId('rekening_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->unsignedBigInteger('nominal');

            // relasi ke pembayaran (opsional)
            $table->foreignId('pembayaran_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('sumber')->nullable();

            $table->date('tanggal');

            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(['tipe', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
