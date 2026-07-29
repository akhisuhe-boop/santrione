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
     *
     * Index lama tidak bisa langsung di-drop karena kolom
     * kelas_id juga dipakai oleh foreign key ke tabel kelas, dan
     * MySQL memakai index unique lama itu sebagai penyangga FK
     * tersebut. Jadi index baru harus dibuat DULU (di ALTER TABLE
     * terpisah) sebelum index lama di-drop, supaya MySQL selalu
     * punya index pengganti untuk FK-nya.
     */
    public function up(): void
    {
        // 1) Buat index baru dulu (ALTER TABLE terpisah).
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->unique(['kelas_id', 'hari', 'jam_ke'], 'jadwal_kelas_unique');
            $table->unique(['pegawai_id', 'hari', 'jam_ke'], 'jadwal_guru_unique');
        });

        // 2) Baru drop index lama (ALTER TABLE terpisah), setelah
        //    index baru di atas sudah siap menggantikan perannya
        //    sebagai penyangga foreign key.
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->dropUnique('jadwal_pelajarans_kelas_id_hari_jam_ke_unique');
            $table->dropUnique('jadwal_pelajarans_pegawai_id_hari_jam_ke_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->unique(['kelas_id', 'hari', 'jam_ke'], 'jadwal_pelajarans_kelas_id_hari_jam_ke_unique');
            $table->unique(['pegawai_id', 'hari', 'jam_ke'], 'jadwal_pelajarans_pegawai_id_hari_jam_ke_unique');
        });

        Schema::table('jadwal_pelajarans', function (Blueprint $table) {
            $table->dropUnique('jadwal_kelas_unique');
            $table->dropUnique('jadwal_guru_unique');
        });
    }
};
