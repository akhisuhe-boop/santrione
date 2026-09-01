<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracking "siapa yang memproses transaksi ini" untuk keperluan
     * laporan per-kasir. Kolom kasir_id yang sudah ada menunjuk ke
     * tabel pegawais, tapi akun yang login ke panel Kasir adalah akun
     * User (admin/staf operasional) -- tidak ada relasi User->Pegawai
     * di skema ini. Jadi dipakai pola yang sama persis dengan
     * Kas.diinput_oleh (nama sebagai string, bukan FK) daripada
     * memaksakan kasir_id yang tipenya tidak cocok.
     */
    public function up(): void
    {
        Schema::table('kantin_transaksis', function (Blueprint $table) {
            $table->string('diinput_oleh')->nullable()->after('kasir_id');
        });
    }

    public function down(): void
    {
        Schema::table('kantin_transaksis', function (Blueprint $table) {
            $table->dropColumn('diinput_oleh');
        });
    }
};
