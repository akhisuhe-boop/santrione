<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kurikulums', function (Blueprint $table) {

            $table->unsignedTinyInteger('jp_per_pertemuan')
                ->default(2)
                ->after('jumlah_jam_per_minggu');

        });
    }

    public function down(): void
    {
        Schema::table('kurikulums', function (Blueprint $table) {

            $table->dropColumn('jp_per_pertemuan');

        });
    }
};