<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {

            // target_type
            if (!Schema::hasColumn('announcements', 'target_type')) {
                $table->enum('target_type', ['all', 'role', 'kelas', 'custom'])
                    ->default('all')
                    ->after('content');
            }

            // target_role
            if (!Schema::hasColumn('announcements', 'target_role')) {
                $table->string('target_role')->nullable()
                    ->after('target_type');
            }

            // send_whatsapp
            if (!Schema::hasColumn('announcements', 'send_whatsapp')) {
                $table->boolean('send_whatsapp')->default(false)
                    ->after('kelas_id');
            }

        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {

            if (Schema::hasColumn('announcements', 'target_type')) {
                $table->dropColumn('target_type');
            }

            if (Schema::hasColumn('announcements', 'target_role')) {
                $table->dropColumn('target_role');
            }

            if (Schema::hasColumn('announcements', 'send_whatsapp')) {
                $table->dropColumn('send_whatsapp');
            }
        });
    }
};