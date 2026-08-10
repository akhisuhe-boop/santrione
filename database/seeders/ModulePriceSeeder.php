<?php

namespace Database\Seeders;

use App\Models\ModulePrice;
use App\Support\FeatureGate;
use Illuminate\Database\Seeder;

/**
 * Harga per dokumen "Skema Pembiayaan Qinara Apps" (revisi terakhir).
 * Master Data, Manajemen Sekolah & Master Setting SENGAJA tidak ada
 * di sini — ketiganya masuk "Akses Platform" (biaya dasar wajib),
 * bukan modul add-on. Jalankan ulang aman (updateOrCreate by key).
 */
class ModulePriceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [FeatureGate::KEUANGAN, 'Keuangan (SPP & Tagihan)', 0, 'wali_murid', true, 10],
            [FeatureGate::E_KANTIN, 'e-Kantin', 0, 'wali_murid', true, 20],
            [FeatureGate::AKADEMIK, 'Akademik', 150_000, 'sekolah', false, 30],
            [FeatureGate::ABSENSI, 'Absensi', 150_000, 'sekolah', false, 40],
            [FeatureGate::PSB, 'PSB (Pendaftaran Siswa Baru)', 150_000, 'sekolah', false, 50],
            [FeatureGate::TAHFIDZ, 'Tahfidz', 99_000, 'sekolah', false, 60],
            [FeatureGate::PERIZINAN, 'Perizinan', 99_000, 'sekolah', false, 70],
            [FeatureGate::KONSELING, 'Konseling', 99_000, 'sekolah', false, 80],
        ];

        foreach ($data as [$key, $nama, $harga, $bebanKe, $gratis, $urutan]) {
            ModulePrice::updateOrCreate(
                ['key' => $key],
                [
                    'nama' => $nama,
                    'harga_bulanan' => $harga,
                    'dibebankan_ke' => $bebanKe,
                    'is_gratis' => $gratis,
                    'is_active' => true,
                    'urutan' => $urutan,
                ]
            );
        }
    }
}
