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
        Schema::create('pegawais', function (Blueprint $table) {
        $table->id();

        $table->string('nama');
        $table->string('nik')->nullable();

        $table->enum('jenis_kelamin', ['L','P']);

        $table->string('no_hp')->nullable();
        $table->string('email')->nullable();
        $table->text('alamat')->nullable();

        $table->string('pendidikan')->nullable();
        $table->date('tanggal_masuk')->nullable();

        $table->string('foto')->nullable();

        $table->boolean('is_active')->default(true);

        $table->timestamps();
    });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
