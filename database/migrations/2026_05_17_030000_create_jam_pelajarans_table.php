<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_pelajarans', function (Blueprint $table) {

            $table->id();
            $table->foreignId('lembaga_id')
            ->constrained()
            ->cascadeOnDelete();

            $table->string('nama');

            $table->time('jam_mulai');

            $table->time('jam_selesai');

            $table->integer('durasi_jp')->default(2);

            $table->integer('urutan');

            $table->boolean('aktif')->default(true);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_pelajarans');
    }
};