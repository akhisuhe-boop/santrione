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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            // Relasi
        $table->foreignId('lembaga_id')->constrained()->cascadeOnDelete();
        $table->foreignId('kelas_id')->constrained()->cascadeOnDelete();

            // 1. Identitas
        $table->string('rfid')->nullable();
        $table->string('nis')->unique();
        $table->string('nisn')->nullable();
        $table->string('nik')->nullable();
        $table->string('nama_lengkap');
        $table->enum('jenis_kelamin', ['L', 'P']);

        // 2. Kelahiran
        $table->string('tempat_lahir')->nullable();
        $table->date('tanggal_lahir')->nullable();

        // 3. Fisik
        $table->integer('tinggi_badan')->nullable();
        $table->integer('berat_badan')->nullable();
        $table->string('golongan_darah')->nullable();

        // 4. Alamat
        $table->string('alamat_jalan')->nullable();
        $table->string('rt')->nullable();
        $table->string('rw')->nullable();
        $table->string('provinsi')->nullable();
        $table->string('kabupaten')->nullable();
        $table->string('kecamatan')->nullable();
        $table->string('desa')->nullable();
        $table->string('kode_pos')->nullable();

        // 5. Orang tua
        $table->string('no_kartu_keluarga')->nullable();
        $table->string('nik_ayah')->nullable();
        $table->string('nama_ayah')->nullable();
        $table->enum('status_ayah', ['Hidup', 'Meninggal', 'Cerai'])->nullable();
        $table->string('pekerjaan_ayah')->nullable();
        $table->string('pendidikan_ayah')->nullable();
        $table->string('penghasilan_ayah')->nullable();
        $table->string('wa_ayah')->nullable();

        $table->string('nik_ibu')->nullable();
        $table->string('nama_ibu')->nullable();
        $table->enum('status_ibu', ['Hidup', 'Meninggal', 'Cerai'])->nullable();
        $table->string('pekerjaan_ibu')->nullable();
        $table->string('pendidikan_ibu')->nullable();
        $table->string('penghasilan_ibu')->nullable();
        $table->string('wa_ibu')->nullable();

        // 6. Dokumen
        $table->string('foto')->nullable();
        $table->string('scan_kk')->nullable();
        $table->string('scan_akta')->nullable();
        $table->string('scan_ijazah')->nullable();

        // 7. Status
        $table->enum('status_siswa', ['Aktif', 'Lulus', 'Pindah'])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
