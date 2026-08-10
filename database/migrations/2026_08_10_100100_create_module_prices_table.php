<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master harga modul add-on (skema "bayar sesuai yang dipakai"),
     * lihat dokumen "Skema Pembiayaan Qinara Apps".
     *
     * `key` HARUS persis salah satu konstanta di App\Support\FeatureGate
     * (mis. 'akademik', 'absensi', 'tahfidz', dst) — key inilah yang
     * dipakai untuk translasi ke $navigationGroup Filament Resource
     * (buka/kunci menu sidebar). Master Data, Manajemen Sekolah, dan
     * Master Setting SENGAJA tidak ada baris di sini — ketiganya
     * termasuk dalam "Akses Platform" (biaya dasar wajib), bukan
     * modul add-on yang dipilih terpisah.
     */
    public function up(): void
    {
        Schema::create('module_prices', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->string('nama');

            $table->unsignedInteger('harga_bulanan')->default(0);

            // sekolah  : ditagih flat ke Yayasan/Lembaga tiap bulan
            // wali_murid : Rp0 ke sekolah, dimonetisasi lewat fee
            //              transaksi payment gateway (Keuangan, e-Kantin)
            $table->enum('dibebankan_ke', ['sekolah', 'wali_murid'])->default('sekolah');

            $table->boolean('is_gratis')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_prices');
    }
};
