<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Constraint unique bawaan Laravel bernama
     * jadwal_pelajarans_kelas_id_hari_jam_ke_unique dan
     * jadwal_pelajarans_pegawai_id_hari_jam_ke_unique — tidak
     * pernah cocok dengan pengecekan 'jadwal_kelas_unique' /
     * 'jadwal_guru_unique' di ListJadwalPelajarans::save(),
     * sehingga pesan bentrok jadwal yang ramah tidak pernah
     * muncul. Migration ini mengganti nama constraint-nya saja
     * (tanpa mengubah kolom yang dipakai) supaya cocok dengan
     * kode aplikasi.
     */
    public function up(): void
    {
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->dropUnique('jadwal_pelajarans_kelas_id_hari_jam_ke_unique');
            $table->dropUnique('jadwal_pelajarans_pegawai_id_hari_jam_ke_unique');

            $table->unique(['kelas_id', 'hari', 'jam_ke'], 'jadwal_kelas_unique');
            $table->unique(['pegawai_id', 'hari', 'jam_ke'], 'jadwal_guru_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->dropUnique('jadwal_kelas_unique');
            $table->dropUnique('jadwal_guru_unique');

            $table->unique(['kelas_id', 'hari', 'jam_ke'], 'jadwal_pelajarans_kelas_id_hari_jam_ke_unique');
            $table->unique(['pegawai_id', 'hari', 'jam_ke'], 'jadwal_pelajarans_pegawai_id_hari_jam_ke_unique');
        });
    }
};
