<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('content');

            // target pengumuman
            $table->enum('target', ['all', 'wali', 'siswa', 'guru'])->default('all');

            // kalau spesifik kelas (opsional)
            $table->foreignId('kelas_id')->nullable()->constrained()->nullOnDelete();

            // siapa yang membuat
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // opsional fitur tambahan
            $table->boolean('is_pinned')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};