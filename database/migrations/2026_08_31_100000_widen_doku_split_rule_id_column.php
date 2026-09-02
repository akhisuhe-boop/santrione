<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DIPERBAIKI -- ditemukan di sandbox: migration sebelumnya
 * (add_doku_split_rule_columns) membuat kolom `doku_split_rule_id`
 * varchar(36), mengikuti `maxLength: 36` yang tertulis di OpenAPI spec
 * resmi DOKU untuk field `splitRuleId`. Ternyata respons sandbox
 * sungguhan mengembalikan nilai dengan prefix "split-rule-" di depan
 * UUID-nya (mis. "split-rule-f5827a67-98a2-474d-b3b4-fedcc02d7314", 49
 * karakter) -- BEDA dari spec (yang kemungkinan cuma mendokumentasikan
 * UUID mentahnya, tanpa prefix). Diperlebar ke varchar(64) supaya ada
 * ruang aman untuk variasi format seperti ini di masa depan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->string('doku_split_rule_id', 64)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lembagas', function (Blueprint $table) {
            $table->string('doku_split_rule_id', 36)->nullable()->change();
        });
    }
};
