<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- tabel diskon volume berdasar TOTAL SISWA se-Yayasan
 * (pengganti diskon bertingkat per-urutan-Lembaga yang lama). Admin
 * bisa atur berapa tier-nya, batasnya di mana, berapa persennya --
 * semua dinamis, tidak hardcode di kode.
 *
 * siswa_max NULLABLE = tidak terbatas ke atas (tier paling tinggi,
 * mis. "700+"). Validasi gap/tumpang-tindih dilakukan di level
 * Filament Resource (bukan di sini) -- migration cuma bentuk tabelnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diskon_volume_siswas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('siswa_min');
            $table->unsignedInteger('siswa_max')->nullable();
            $table->unsignedTinyInteger('diskon_persen');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diskon_volume_siswas');
    }
};
