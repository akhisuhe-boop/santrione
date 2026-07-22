<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tunjangan/potongan TETAP: diisi 1x per pegawai (mis. Tunjangan
     * Wali Kelas, Tunjangan Pembina Eskul), lalu otomatis ikut
     * ditambahkan ke payroll_adjustments SETIAP BULAN saat payroll
     * di-generate — tidak perlu diinput ulang tiap bulan. Adjustment
     * yang sifatnya dinamis/berubah-ubah (mis. bonus/potongan
     * insidental) tetap diinput manual langsung di payroll_adjustments
     * seperti biasa.
     */
    public function up(): void
    {
        Schema::create('payroll_adjustment_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pegawai_id')
                ->constrained('pegawais')
                ->cascadeOnDelete();

            $table->enum('tipe', [
                'tambahan',
                'potongan',
            ]);

            $table->string('nama_komponen');

            $table->bigInteger('nominal')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->text('catatan')
                ->nullable();

            $table->timestamps();
        });

        Schema::table('payroll_adjustments', function (Blueprint $table) {
            $table->foreignId('source_template_id')
                ->nullable()
                ->after('payroll_id')
                ->constrained('payroll_adjustment_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_adjustments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_template_id');
        });

        Schema::dropIfExists('payroll_adjustment_templates');
    }
};
