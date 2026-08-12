<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pola sama seperti notification_type_toggles: TIDAK ADA baris
     * untuk (lembaga_id, key) -> pakai redaksi DEFAULT (string
     * hardcode yang sudah ada di NotificationService, dipakai sebagai
     * $default lewat method render()). Baris cuma dibuat kalau
     * sekolah EKSPLISIT ubah redaksinya sendiri.
     */
    public function up(): void
    {
        Schema::create('lembaga_notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lembaga_id')->constrained('lembagas')->cascadeOnDelete();
            $table->string('key'); // cocok dengan App\Support\NotificationType
            $table->text('template');
            $table->timestamps();

            $table->unique(['lembaga_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembaga_notification_templates');
    }
};
