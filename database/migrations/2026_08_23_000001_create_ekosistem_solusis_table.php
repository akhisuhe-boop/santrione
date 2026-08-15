<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekosistem_solusis', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->default('sparkles'); // nama icon lucide.dev
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('tag_text')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekosistem_solusis');
    }
};
