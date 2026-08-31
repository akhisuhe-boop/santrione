<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DITAMBAHKAN -- setelah dicek ulang ke developers.doku.com/wallet-as-a-
 * service/sub-account/sub-account-v2/integration-guide (OpenAPI spec
 * resmi Sub Account V2), ditemukan 2 hal yang BELUM tertampung di skema
 * lama (kolom `doku_sub_account_id` di migration
 * add_doku_and_fix_gateway_columns):
 *
 * 1. Response Register Sub Account V2 mengembalikan DUA identifier
 *    berbeda, bukan satu:
 *    - `profileId` (format "SAC-xxxx-xxxxxxxxxxxxx") -- dipakai di
 *      `additionalInfo.account.id` saat Accept Payment (lihat
 *      DokuService::buatPaymentRequest()/buatVaLangsung()), dan sebagai
 *      referensi umum ke sub-account di hampir semua endpoint lain.
 *    - `accounts[].accountNo` (10 digit, per tipe akun: DOKU_MERCHANT_IDR
 *      / DOKU_PENDING_IDR / DOKU_MERCHANT_POINT) -- WAJIB dipakai
 *      sebagai `accountNumber` di Split Rule items (Create Split Rules),
 *      dan sebagai `fromAccount`/`accountNo` di Balance Inquiry, Transfer,
 *      dan Transaction History.
 *    Kolom lama `doku_sub_account_id` DIPERTAHANKAN dan tetap dipakai
 *    untuk menyimpan `profileId` (tidak ada breaking change ke kode yang
 *    sudah pakai kolom ini) -- kolom baru `doku_account_no` menyimpan
 *    accountNo dari account type DOKU_MERCHANT_IDR-nya.
 *
 * 2. Split Rule (POST /sub-account/v2.0/split-rules) TIDAK PERNAH
 *    diimplementasikan sama sekali sebelumnya -- method
 *    `registerSubAccount()` lama cuma routing dana PENUH ke sub-account
 *    Lembaga tanpa split apapun (lihat catatan panjang yang sudah
 *    dihapus di DokuService, yang justru SALAH mengira split harus lewat
 *    Debit API belakangan -- padahal dokumentasi resmi DOKU menyediakan
 *    Split Rule yang jalan OTOMATIS di titik pembayaran, mirip Xendit).
 *    Kolom `doku_split_rule_id` menyimpan `splitRuleId` hasil
 *    DokuService::buatSplitRule(), dipakai di `additionalInfo.
 *    account.split_rule_id` setiap kali membuat payment request untuk
 *    Lembaga ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->string('doku_account_no', 10)->nullable()->after('doku_sub_account_id');
            $table->string('doku_split_rule_id', 36)->nullable()->after('doku_account_no');
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->dropColumn(['doku_account_no', 'doku_split_rule_id']);
        });
    }
};
