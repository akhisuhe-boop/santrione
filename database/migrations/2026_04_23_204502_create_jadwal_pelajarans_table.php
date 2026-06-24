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
            Schema::create('jadwal_pelajarans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pegawai_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained()->cascadeOnDelete();

            // 🔥 lebih aman pakai enum
            $table->enum('hari', [
                'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'
            ]);

            $table->integer('jam_ke');
            $table->integer('durasi_jam')->default(1);

            $table->string('mata_pelajaran');

            $table->timestamps();

            // 🔥 cegah bentrok kelas
            $table->unique(['kelas_id', 'hari', 'jam_ke']);

            // 🔥 cegah bentrok guru
            $table->unique(['pegawai_id', 'hari', 'jam_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajarans');
    }
};
