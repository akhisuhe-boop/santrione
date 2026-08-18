<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BUG FIX: kolom ini sebelumnya TIDAK nullable, padahal field di form
     * dimaksudkan opsional ("kosongkan = tidak ada diskon", sama seperti
     * harga_per_siswa_tambahan/harga_per_lembaga_tambahan di tabel yang
     * sama, yang memang sudah nullable). Begitu admin mengosongkan
     * field-nya di form, Filament kirim NULL ke database -- kolom yang
     * tidak nullable menolak itu dan menghasilkan 500 Server Error.
     */
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedTinyInteger('diskon_tahunan_persen')->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedTinyInteger('diskon_tahunan_persen')->nullable(false)->default(0)->change();
        });
    }
};
