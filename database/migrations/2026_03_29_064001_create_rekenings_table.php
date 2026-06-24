<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekenings', function (Blueprint $table) {
            $table->id();

            $table->string('nama'); // BSI Operasional
            $table->string('bank')->nullable();
            $table->string('no_rekening')->nullable();
            $table->string('atas_nama')->nullable();

            $table->enum('tipe', ['bank', 'cash', 'ewallet'])->default('bank');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekenings');
    }
};
