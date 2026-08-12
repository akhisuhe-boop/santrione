<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // 'tagihan_subscription', 'trial_reminder', dst
            $table->string('nama'); // label yang tampil di panel
            $table->text('template'); // isi pesan, pakai {placeholder}
            $table->text('keterangan_placeholder')->nullable(); // dokumentasi placeholder apa saja yang tersedia untuk template ini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
