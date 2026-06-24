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
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->enum('method', ['bank', 'ewallet', 'cash'])->default('bank');
            $table->string('rekening_tujuan');
            $table->string('nama_pemilik');

            $table->enum('requested_by_type', ['siswa', 'orang_tua', 'admin']);
            $table->unsignedBigInteger('requested_by_id');

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->text('catatan_admin')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};
