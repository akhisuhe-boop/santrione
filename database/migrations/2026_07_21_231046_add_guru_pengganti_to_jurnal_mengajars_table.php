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
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->foreignId('pegawai_asli_id')->nullable()->after('pegawai_id')->constrained('pegawais')->nullOnDelete();
            $table->decimal('tarif_pengganti_per_jp', 12, 2)->nullable()->after('pegawai_asli_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jurnal_mengajars', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pegawai_asli_id');
            $table->dropColumn('tarif_pengganti_per_jp');
        });
    }
};
