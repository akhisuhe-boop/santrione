<?php

namespace Tests\Unit;

use App\Models\Lembaga;
use App\Models\LembagaModule;
use App\Models\ModulePrice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Yayasan;
use App\Services\TenantBillingCalculator;
use Database\Seeders\ModulePriceSeeder;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mengunci angka-angka di dokumen "Skema Pembiayaan Qinara Apps"
 * (Contoh 1-5) sebagai regression test. Kalau suatu saat rumus di
 * TenantBillingCalculator berubah dan angka-angka ini ikut berubah
 * TANPA disengaja, test ini yang akan gagal duluan — bukan ditemukan
 * belakangan oleh sekolah yang menghitung manual seperti kejadian
 * sebelumnya (Contoh 5 di draf dokumen sempat salah total Rp163.350
 * karena baris Lembaga ke-4 terhapus tapi totalnya lupa dihitung ulang).
 */
class TenantBillingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected TenantBillingCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubscriptionPlanSeeder::class);
        $this->seed(ModulePriceSeeder::class);

        $this->calculator = app(TenantBillingCalculator::class);
    }

    protected function buatYayasanDenganAksesPlatform(): Yayasan
    {
        $yayasan = Yayasan::create(['nama' => 'Yayasan Uji', 'status' => 'active']);

        $plan = SubscriptionPlan::where('slug', 'akses-platform')->firstOrFail();

        $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'mulai_pada' => now()->subDay(),
            'berakhir_pada' => now()->addMonth(),
        ]);

        return $yayasan->fresh();
    }

    protected function buatLembaga(Yayasan $yayasan, int $jumlahSiswa, array $moduleKeys = []): Lembaga
    {
        $lembaga = Lembaga::create([
            'yayasan_id' => $yayasan->id,
            'nama' => 'Lembaga ' . $yayasan->lembagas()->count() + 1,
            'jenis' => 'SD',
        ]);

        $lembaga->update(['jumlah_siswa_billing' => $jumlahSiswa]);

        foreach ($moduleKeys as $key) {
            $mp = ModulePrice::where('key', $key)->firstOrFail();

            LembagaModule::create([
                'lembaga_id' => $lembaga->id,
                'module_price_id' => $mp->id,
                'is_active' => true,
                'aktif_sejak' => now(),
            ]);
        }

        return $lembaga->fresh();
    }

    /** Contoh 1 — TK/RA Kecil, 60 Siswa: Keuangan saja -> Rp99.000 */
    public function test_contoh_1_tk_kecil_60_siswa(): void
    {
        $yayasan = $this->buatYayasanDenganAksesPlatform();
        $lembaga = $this->buatLembaga($yayasan, 60, ['keuangan']);

        $hasil = $this->calculator->hitungLembaga($lembaga);

        $this->assertSame(99_000, $hasil['subtotal']);
    }

    /** Contoh 2 — SD/MI Kecil, 120 Siswa: Keuangan + Absensi -> Rp269.000 */
    public function test_contoh_2_sd_kecil_120_siswa(): void
    {
        $yayasan = $this->buatYayasanDenganAksesPlatform();
        $lembaga = $this->buatLembaga($yayasan, 120, ['keuangan', 'absensi']);

        $hasil = $this->calculator->hitungLembaga($lembaga);

        // 99.000 + (20 x 1.000) + 150.000 (Absensi) + 0 (Keuangan)
        $this->assertSame(269_000, $hasil['subtotal']);
    }

    /** Contoh 3 — SMP/MTs Menengah, 300 Siswa -> Rp749.000 */
    public function test_contoh_3_smp_menengah_300_siswa(): void
    {
        $yayasan = $this->buatYayasanDenganAksesPlatform();
        $lembaga = $this->buatLembaga($yayasan, 300, ['keuangan', 'akademik', 'absensi', 'psb']);

        $hasil = $this->calculator->hitungLembaga($lembaga);

        // 99.000 + (200 x 1.000) + 150.000x3 (Akademik+Absensi+PSB)
        $this->assertSame(749_000, $hasil['subtotal']);
    }

    /** Contoh 4 — Lembaga Besar 500 Siswa, Paket Full -> Rp910.000 */
    public function test_contoh_4_paket_full_500_siswa(): void
    {
        $yayasan = Yayasan::create(['nama' => 'Yayasan Full', 'status' => 'active']);
        $planFull = SubscriptionPlan::where('slug', 'paket-full')->firstOrFail();

        $yayasan->subscriptions()->create([
            'subscription_plan_id' => $planFull->id,
            'status' => 'active',
            'mulai_pada' => now()->subDay(),
            'berakhir_pada' => now()->addMonth(),
        ]);

        $allModules = ['keuangan', 'e_kantin', 'akademik', 'absensi', 'psb', 'tahfidz', 'perizinan', 'konseling'];
        $lembaga = $this->buatLembaga($yayasan->fresh(), 500, $allModules);

        $hasil = $this->calculator->hitungLembaga($lembaga);

        // Akses Platform Full: 590.000 + (400 x 800) = 910.000
        // Semua modul Rp0 (termasuk_semua_modul = true, tidak dobel hitung)
        $this->assertSame(910_000, $hasil['subtotal']);

        foreach ($hasil['modul'] as $m) {
            if (! in_array($m['key'], ['keuangan', 'e_kantin'], true)) {
                $this->assertTrue($m['termasuk_paket_full']);
                $this->assertSame(0, $m['harga']);
            }
        }
    }

    /**
     * Contoh 5 — Yayasan dengan 3 Lembaga (versi final dokumen upload
     * terakhir: SDIT, MTs, MA — 350 siswa) -> total HARUS SAMA DENGAN
     * array_sum() subtotal ketiganya, TIDAK BOLEH ada angka terpisah
     * yang tidak sinkron (ini akar masalah yang ditemukan sebelumnya).
     */
    public function test_contoh_5_yayasan_multi_lembaga_total_selalu_sinkron(): void
    {
        $yayasan = $this->buatYayasanDenganAksesPlatform();

        $sdit = $this->buatLembaga($yayasan, 150, ['keuangan', 'absensi', 'tahfidz']);
        $mts = $this->buatLembaga($yayasan, 120, ['keuangan', 'akademik', 'absensi']);
        $ma = $this->buatLembaga($yayasan, 80, ['keuangan', 'akademik', 'absensi', 'psb']);

        $hasilSdit = $this->calculator->hitungLembaga($sdit);
        $hasilMts = $this->calculator->hitungLembaga($mts);
        $hasilMa = $this->calculator->hitungLembaga($ma);

        $this->assertSame(398_000, $hasilSdit['subtotal']); // 149.000 + 249.000
        $this->assertSame(395_200, $hasilMts['subtotal']);  // 95.200 + 300.000
        $this->assertSame(529_200, $hasilMa['subtotal']);   // 79.200 + 450.000

        $hasilYayasan = $this->calculator->hitungYayasan($yayasan->fresh());

        // INVARIAN UTAMA: total Yayasan = array_sum() subtotal Lembaga,
        // dihitung otomatis oleh service, bukan ditulis manual.
        $this->assertSame(
            $hasilSdit['subtotal'] + $hasilMts['subtotal'] + $hasilMa['subtotal'],
            $hasilYayasan['total']
        );
        $this->assertSame(1_322_400, $hasilYayasan['total']);
    }

    /** Diskon volume: Lembaga ke-4 dan seterusnya harus 35%, bukan 20%. */
    public function test_diskon_lembaga_keempat_35_persen(): void
    {
        $yayasan = $this->buatYayasanDenganAksesPlatform();

        $this->buatLembaga($yayasan, 60, ['keuangan']);
        $this->buatLembaga($yayasan, 60, ['keuangan']);
        $this->buatLembaga($yayasan, 60, ['keuangan']);
        $keempat = $this->buatLembaga($yayasan, 52, ['keuangan', 'tahfidz']);

        $hasil = $this->calculator->hitungLembaga($keempat);

        $this->assertSame(35, $hasil['diskon_persen']);
        // 99.000 x 0.65 = 64.350 + 99.000 (Tahfidz) = 163.350
        $this->assertSame(163_350, $hasil['subtotal']);
    }
}
