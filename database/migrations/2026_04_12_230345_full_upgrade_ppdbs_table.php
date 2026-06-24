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
        Schema::table('ppdbs', function (Blueprint $table) {

    // ======================
    // IDENTITAS
    // ======================
    $table->string('nama_lengkap')->nullable();
    $table->string('nis')->nullable();
    $table->string('nik')->nullable();
    $table->string('rfid')->nullable();

    // ======================
    // KELAHIRAN
    // ======================
    $table->string('tempat_lahir')->nullable();
    $table->date('tanggal_lahir')->nullable();

    // ======================
    // FISIK
    // ======================
    $table->string('tinggi_badan')->nullable();
    $table->string('berat_badan')->nullable();
    $table->string('golongan_darah')->nullable();

    // ======================
    // ALAMAT
    // ======================
    $table->string('alamat_jalan')->nullable();
    $table->string('provinsi')->nullable();
    $table->string('kabupaten')->nullable();
    $table->string('kecamatan')->nullable();
    $table->string('desa')->nullable();
    $table->string('rt')->nullable();
    $table->string('rw')->nullable();
    $table->string('kode_pos')->nullable();

    // ======================
    // AYAH
    // ======================
    $table->string('no_kartu_keluarga')->nullable();
    $table->string('nik_ayah')->nullable();
    $table->string('nama_ayah')->nullable();
    $table->string('status_ayah')->nullable();
    $table->string('pekerjaan_ayah')->nullable();
    $table->string('pendidikan_ayah')->nullable();
    $table->string('penghasilan_ayah')->nullable();
    $table->string('wa_ayah')->nullable();

    // ======================
    // IBU
    // ======================
    $table->string('nik_ibu')->nullable();
    $table->string('nama_ibu')->nullable();
    $table->string('status_ibu')->nullable();
    $table->string('pekerjaan_ibu')->nullable();
    $table->string('pendidikan_ibu')->nullable();
    $table->string('penghasilan_ibu')->nullable();
    $table->string('wa_ibu')->nullable();

    // ======================
    // WALI
    // ======================
    $table->string('nik_wali')->nullable();
    $table->string('nama_wali')->nullable();
    $table->string('status_wali')->nullable();
    $table->string('pekerjaan_wali')->nullable();
    $table->string('pendidikan_wali')->nullable();
    $table->string('penghasilan_wali')->nullable();
    $table->string('hubungan_wali')->nullable();
    $table->string('wa_wali')->nullable();

    // ======================
    // DOKUMEN
    // ======================
    $table->string('foto')->nullable();
    $table->string('scan_kk')->nullable();
    $table->string('scan_akta')->nullable();
    $table->string('scan_ijazah')->nullable();

    // ======================
    // PENEMPATAN
    // ======================
    $table->foreignId('lembaga_id')->nullable()->constrained();
    $table->foreignId('kelas_id')->nullable()->constrained();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('ppdbs', function (Blueprint $table) {
        $table->dropColumn([
            'nama_lengkap',
            'nis',
            'nik',
            'rfid',
            'tempat_lahir',
            'tanggal_lahir',
            'tinggi_badan',
            'berat_badan',
            'golongan_darah',
            'alamat_jalan',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'desa',
            'rt',
            'rw',
            'kode_pos',
            'no_kartu_keluarga',
            'nik_ayah',
            'nama_ayah',
            'status_ayah',
            'pekerjaan_ayah',
            'pendidikan_ayah',
            'penghasilan_ayah',
            'wa_ayah',
            'nik_ibu',
            'nama_ibu',
            'status_ibu',
            'pekerjaan_ibu',
            'pendidikan_ibu',
            'penghasilan_ibu',
            'wa_ibu',
            'nik_wali',
            'nama_wali',
            'status_wali',
            'pekerjaan_wali',
            'pendidikan_wali',
            'penghasilan_wali',
            'hubungan_wali',
            'wa_wali',
            'foto',
            'scan_kk',
            'scan_akta',
            'scan_ijazah',
        ]);

        $table->dropForeign(['lembaga_id']);
        $table->dropForeign(['kelas_id']);
        $table->dropColumn(['lembaga_id', 'kelas_id']);
    });
}
};
