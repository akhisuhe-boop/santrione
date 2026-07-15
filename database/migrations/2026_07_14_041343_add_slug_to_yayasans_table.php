<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Yayasan;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('nama');
        });

        // Isi slug untuk data yayasan yang sudah ada, berdasarkan nama.
        // Pakai withoutGlobalScopes() supaya migration ini tidak
        // terpengaruh tenant scope (tidak ada user login saat migration).
        Yayasan::withoutGlobalScopes()->get()->each(function ($yayasan) {
            $base = Str::slug($yayasan->nama);
            $slug = $base;
            $i = 1;

            while (Yayasan::withoutGlobalScopes()->where('slug', $slug)->where('id', '!=', $yayasan->id)->exists()) {
                $slug = $base . '-' . $i;
                $i++;
            }

            $yayasan->update(['slug' => $slug]);
        });

        Schema::table('yayasans', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('yayasans', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
