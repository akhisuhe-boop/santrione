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
        Schema::create('nilais', function (Blueprint $table) {

            $table->id();

            $table->foreignId('siswa_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('kelas_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('mapel_id')
                ->constrained('mata_pelajarans')
                ->cascadeOnDelete();

            $table->foreignId('guru_id')
                ->nullable()
                ->constrained('pegawais')
                ->nullOnDelete();

            // semester sudah menyatu di tahun_ajarans
            $table->foreignId('tahun_ajaran_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('tipe_nilai', [
                'tugas',
                'harian',
                'uts',
                'uas',
            ]);

            // contoh:
            // Tugas Bab 1
            // Ulangan Pecahan
            $table->string('judul')->nullable();

            $table->decimal('nilai', 5, 2);

            $table->text('catatan')->nullable();

            // optional future-proof
            $table->boolean('is_publish')
                ->default(false);

            $table->index([
                'siswa_id',
                'mapel_id',
                'tahun_ajaran_id',
                'tipe_nilai',
            ]);

            $table->unique([
                'siswa_id',
                'mapel_id',
                'tahun_ajaran_id',
                'tipe_nilai',
                'judul',
            ], 'nilai_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};