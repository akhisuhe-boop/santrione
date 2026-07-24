<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kantin_produks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lembaga_id')
                ->constrained('lembagas')
                ->cascadeOnDelete();

            $table->string('nama');
            $table->string('kategori')->nullable(); // Makanan / Minuman / Snack / dll (bebas teks)
            $table->bigInteger('harga');
            $table->unsignedInteger('stok')->nullable(); // null = tidak dibatasi/tidak dilacak
            $table->string('gambar')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kantin_produks');
    }
};
