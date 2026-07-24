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
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('topup','payment','withdraw','refund','adjustment','kantin') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_transactions MODIFY COLUMN type ENUM('topup','payment','withdraw','refund') NOT NULL");
    }
};
