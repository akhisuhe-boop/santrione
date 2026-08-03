<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absensi_harians MODIFY status_masuk ENUM('Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE absensi_harians MODIFY status_masuk ENUM('Hadir', 'Terlambat') NULL");
    }
};
