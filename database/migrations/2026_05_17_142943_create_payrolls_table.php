<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {

            $table->id();

            $table->foreignId('pegawai_id')
                ->constrained('pegawais')
                ->cascadeOnDelete();

            $table->integer('bulan');

            $table->integer('tahun');

            $table->bigInteger('subtotal')
                ->default(0);

            $table->bigInteger('bonus')
                ->default(0);

            $table->bigInteger('potongan')
                ->default(0);

            $table->bigInteger('total_gaji')
                ->default(0);

            $table->enum('status', [
                'draft',
                'disetujui',
                'dibayar'
            ])->default('draft');

            $table->date('tanggal_bayar')
                ->nullable();

            $table->text('catatan')
                ->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};