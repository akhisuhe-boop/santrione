<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modul_aplikasis', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('layout-dashboard'); // nama icon lucide.dev
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('tag_text')->nullable();
            $table->boolean('is_featured')->default(false); // kartu full-width, gaya seperti "Absensi Digital"
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modul_aplikasis');
    }
};
