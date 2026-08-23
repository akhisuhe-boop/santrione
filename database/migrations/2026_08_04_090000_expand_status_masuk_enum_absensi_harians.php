<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    // Sintaks ALTER ... MODIFY ... ENUM(...) khusus MySQL, di-skip
    // saat testing (sqlite in-memory) -- SQLite tidak punya konsep
    // ENUM/MODIFY COLUMN, kolomnya sudah longgar tanpa perlu diperlebar.
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE absensi_harians MODIFY status_masuk ENUM('Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpa') NULL");
    }
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE absensi_harians MODIFY status_masuk ENUM('Hadir', 'Terlambat') NULL");
    }
};
