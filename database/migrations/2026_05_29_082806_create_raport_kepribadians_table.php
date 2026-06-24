<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raport_kepribadians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('raport_non_akademik_id')
                ->constrained('raport_non_akademiks')
                ->cascadeOnDelete();

            $table->string('aspek');

            $table->integer('nilai')
                ->nullable();

            $table->string('grade')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_kepribadians');
    }
};