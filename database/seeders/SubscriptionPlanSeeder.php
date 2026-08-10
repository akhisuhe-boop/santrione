<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * Dua plan dasar skema baru — per dokumen "Skema Pembiayaan Qinara
 * Apps". Plan lain (mis. paket flat lama Rintisan/Reguler/Premium
 * kalau masih dipakai) tetap ada terpisah, tidak disentuh seeder ini.
 *
 * CATATAN: harga_per_lembaga_tambahan untuk "Paket Full" (Rp70.000)
 * BELUM eksplisit dikonfirmasi di revisi dokumen terakhir (tabel
 * contoh di dokumen tidak menampilkan baris "tambahan lembaga" untuk
 * Paket Full) — nilai ini asumsi sementara mengikuti proporsi diskon
 * yang sama seperti Akses Platform. Sesuaikan di panel admin
 * (Paket Langganan) begitu angka final dikonfirmasi.
 */
class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'akses-platform'],
            [
                'nama' => 'Akses Platform',
                'deskripsi' => 'Biaya dasar wajib — data sekolah terpusat & aman, dashboard, dan fondasi yang dipakai semua modul lain.',
                'harga_bulanan' => 99_000,
                'maks_lembaga' => 1,
                'maks_siswa' => 100,
                'harga_per_siswa_tambahan' => 1_000,
                'harga_per_lembaga_tambahan' => 100_000,
                'fitur' => [],
                'termasuk_semua_modul' => false,
                'is_active' => true,
                'urutan' => 10,
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'paket-full'],
            [
                'nama' => 'Paket Full',
                'deskripsi' => 'Akses Platform + semua modul termasuk, lebih hemat dibanding bayar satu-satu.',
                'harga_bulanan' => 590_000,
                'maks_lembaga' => 1,
                'maks_siswa' => 100,
                'harga_per_siswa_tambahan' => 800,
                'harga_per_lembaga_tambahan' => 70_000,
                'fitur' => [
                    'master_data', 'manajemen_sekolah', 'psb', 'keuangan',
                    'akademik', 'absensi', 'tahfidz', 'e_kantin',
                    'perizinan', 'konseling', 'master_setting',
                ],
                'termasuk_semua_modul' => true,
                'is_active' => true,
                'urutan' => 20,
            ]
        );
    }
}
