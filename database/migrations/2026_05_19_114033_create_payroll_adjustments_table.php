<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('tipe', [
                'tambahan',
                'potongan',
            ]);

            $table->string('nama_komponen');
            $table->integer('qty')
                ->default(1);
            $table->bigInteger('nominal')
                ->default(0);
            $table->bigInteger('subtotal')
                ->default(0);
            $table->text('catatan')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_adjustments');
    }
};