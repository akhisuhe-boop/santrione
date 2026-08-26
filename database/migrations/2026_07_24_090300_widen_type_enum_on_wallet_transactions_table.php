<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * 'adjustment' sudah dipakai WalletService::logAdjustment() sejak
     * sebelum saya masuk, tapi ternyata tidak pernah ada di enum aslinya
     * — jadi ini sekaligus memperbaiki bug laten itu. 'kantin' baru
     * dipakai fitur e-Kantin.
     *
     * Guard driver MySQL: sintaks ALTER ... MODIFY COLUMN ... ENUM(...)
     * ini khusus MySQL, SQLite tidak punya konsep ini sama sekali
     * (kolomnya sudah longgar tanpa perlu diperlebar) — di-skip saat
     * testing (phpunit.xml pakai sqlite in-memory) supaya migration
     * tetap bisa dijalankan ulang dari nol tanpa error di lingkungan test.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('topup','payment','withdraw','refund','adjustment','kantin') NOT NULL");
    }
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('topup','payment','withdraw','refund') NOT NULL");
    }
};
