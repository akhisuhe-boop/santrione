<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_mengajars', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('pegawai_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained()->cascadeOnDelete();

        // ✅ TAMBAHKAN INI
            $table->foreignId('jadwal_pelajaran_id')
                ->constrained('jadwal_pelajarans')
                ->cascadeOnDelete();

            // Data mengajar
            $table->date('tanggal');
            $table->integer('jam_ke');
            $table->integer('durasi_jam')->default(1);

            $table->text('materi')->nullable();

            // Status
            $table->enum('status', ['draft', 'valid'])->default('draft');

            // Audit validasi (optional tapi disarankan)
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();

            // 🔥 Cegah bentrok jam (WAJIB)
            $table->unique(['pegawai_id', 'tanggal', 'jam_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_mengajars');
    }
};