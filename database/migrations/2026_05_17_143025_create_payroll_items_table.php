<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('payroll_id')
                ->constrained('payrolls')
                ->cascadeOnDelete();

            $table->foreignId('pegawai_lembaga_id')
                ->nullable()
                ->constrained('pegawai_lembaga')
                ->nullOnDelete();

            $table->string('nama_komponen');

            $table->enum('jenis', [
                'gaji',
                'bonus',
                'potongan'
            ])->default('gaji');

            $table->integer('qty')
                ->default(1);

            $table->bigInteger('tarif')
                ->default(0);

            $table->bigInteger('subtotal')
                ->default(0);

            $table->text('keterangan')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};