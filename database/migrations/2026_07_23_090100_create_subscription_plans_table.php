<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();

            $table->bigInteger('harga_bulanan');

            // Batas paket — null artinya tidak dibatasi.
            $table->unsignedInteger('maks_lembaga')->nullable();
            $table->unsignedInteger('maks_siswa')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
