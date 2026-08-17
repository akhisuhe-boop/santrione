<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('prioritas')->default('hangat')->after('status'); // panas, hangat, dingin
            $table->date('next_follow_up_at')->nullable()->after('prioritas');
            $table->text('alasan_batal')->nullable()->after('next_follow_up_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['prioritas', 'next_follow_up_at', 'alasan_batal']);
        });
    }
};
