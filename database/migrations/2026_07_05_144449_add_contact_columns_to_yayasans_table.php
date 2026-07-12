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
        Schema::table('yayasans', function (Blueprint $table) {

            $table->text('alamat')
                ->nullable()
                ->after('logo');

            $table->string('telepon', 30)
                ->nullable()
                ->after('alamat');

            $table->string('email', 100)
                ->nullable()
                ->after('telepon');

            $table->string('website', 150)
                ->nullable()
                ->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {

            $table->dropColumn([
                'alamat',
                'telepon',
                'email',
                'website',
            ]);
        });
    }
};