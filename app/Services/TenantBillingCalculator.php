<?php

namespace App\Services;

use App\Models\Lembaga;
use App\Models\SubscriptionPlan;
use App\Models\Yayasan;

/**
 * Satu-satunya tempat rumus tagihan skema "Skema Pembiayaan Qinara
 * Apps" dihitung — Akses Platform + tambahan siswa/lembaga + modul
 * add-on, dengan diskon volume multi-lembaga.
 *
 * KENAPA SERVICE INI PENTING (bukan sekadar rapi kode): draft dokumen
 * penawaran sebelumnya sempat salah hitung total Yayasan multi-lembaga
 * karena angkanya disalin manual antar revisi tanpa dihitung ulang.
 * Selama TOTAL Yayasan SELALU dihasilkan dari array_sum() atas
 * subtotal per-Lembaga yang baru saja dihitung method ini sendiri
 * (bukan angka terpisah yang bisa "lupa disinkron"), kesalahan jenis
 * itu tidak mungkin terjadi lagi — lihat test
 * tests/Unit/TenantBillingCalculatorTest.php yang menjaga invarian ini.
 */
class TenantBillingCalculator
{
    /** Lembaga 1 = 0% diskon, ke-2 & ke-3 = 20%, ke-4 dst = 35%. */
    protected function diskonPersenUntukUrutan(int $urutanKe): int
    {
        return match (true) {
            $urutanKe <= 1 => 0,
            $urutanKe <= 3 => 20,
            default => 35,
        };
    }

    /**
     * Ambil SubscriptionPlan yang berperan sebagai "Akses Platform"
     * (basis biaya dasar) untuk sebuah Yayasan. Kalau Yayasan punya
     * subscription plan aktif yang memang diisi harga_per_siswa_tambahan
     * (menandakan plan itu memang plan gaya "Akses Platform"), pakai
     * itu. Kalau tidak ada, fallback ke plan default is_active urutan
     * pertama yang punya harga_per_siswa_tambahan terisi.
     */
    protected function aksesPlatformPlan(Yayasan $yayasan): ?SubscriptionPlan
    {
        $subscription = $yayasan->activeSubscription();

        if ($subscription?->plan?->harga_per_siswa_tambahan !== null) {
            return $subscription->plan;
        }

        return SubscriptionPlan::where('is_active', true)
            ->whereNotNull('harga_per_siswa_tambahan')
            ->orderBy('urutan')
            ->first();
    }

    /**
     * Hitung tagihan 1 Lembaga untuk periode berjalan.
     *
     * Dipakai standalone (mis. preview harga di form admin sebelum
     * menyimpan modul) maupun dipanggil berulang oleh hitungYayasan().
     */
    public function hitungLembaga(Lembaga $lembaga): array
    {
        $yayasan = $lembaga->yayasan;
        $plan = $this->aksesPlatformPlan($yayasan);

        $jumlahSiswa = $lembaga->jumlah_siswa_billing ?? $lembaga->jumlahSiswaAktif();

        $hargaDasar = (int) ($plan->harga_bulanan ?? 0);
        $kuotaSiswa = (int) ($plan->maks_siswa ?? 100);
        $hargaPerSiswaTambahan = (int) ($plan->harga_per_siswa_tambahan ?? 0);

        $siswaTambahan = max(0, $jumlahSiswa - $kuotaSiswa);
        $biayaSiswaTambahan = $siswaTambahan * $hargaPerSiswaTambahan;

        $aksesPlatformSebelumDiskon = $hargaDasar + $biayaSiswaTambahan;

        $urutanKe = $lembaga->urutanBillingKe();
        $diskonPersen = $this->diskonPersenUntukUrutan($urutanKe);
        $aksesPlatformSetelahDiskon = (int) round($aksesPlatformSebelumDiskon * (1 - $diskonPersen / 100));

        $modulAktif = $lembaga->activeModules()->get();
        $termasukSemuaModul = (bool) ($plan->termasuk_semua_modul ?? false);

        $rincianModul = $modulAktif->map(function ($lm) use ($termasukSemuaModul) {
            $mp = $lm->modulePrice;

            // Paket Full: modul sudah termasuk di harga Akses Platform
            // di atas — jangan dihitung lagi di sini, atau nominalnya
            // dobel. Tetap dicatat di rincian (harga=0, ditandai
            // 'termasuk_paket') supaya invoice tetap menunjukkan modul
            // apa saja yang aktif untuk Lembaga ini.
            return [
                'key' => $mp->key,
                'nama' => $mp->nama,
                'harga' => $termasukSemuaModul ? 0 : $mp->hargaTagihSekolah(),
                'dibebankan_ke' => $mp->dibebankan_ke,
                'termasuk_paket_full' => $termasukSemuaModul && ! $mp->is_gratis,
            ];
        })->values()->all();

        $totalModul = array_sum(array_column($rincianModul, 'harga'));

        $subtotal = $aksesPlatformSetelahDiskon + $totalModul;

        return [
            'lembaga_id' => $lembaga->id,
            'lembaga_nama' => $lembaga->nama,
            'jumlah_siswa' => $jumlahSiswa,
            'urutan_ke' => $urutanKe,
            'akses_platform_sebelum_diskon' => $aksesPlatformSebelumDiskon,
            'diskon_persen' => $diskonPersen,
            'akses_platform' => $aksesPlatformSetelahDiskon,
            'modul' => $rincianModul,
            'total_modul' => $totalModul,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Hitung tagihan gabungan seluruh Lembaga dalam 1 Yayasan. `total`
     * SELALU dihasilkan dari array_sum() subtotal per-Lembaga di atas
     * — tidak pernah nilai terpisah yang bisa tidak sinkron.
     */
    public function hitungYayasan(Yayasan $yayasan): array
    {
        $rincianLembaga = $yayasan->lembagas()
            ->orderBy('id')
            ->get()
            ->map(fn (Lembaga $lembaga) => $this->hitungLembaga($lembaga))
            ->values()
            ->all();

        $total = array_sum(array_column($rincianLembaga, 'subtotal'));

        return [
            'yayasan_id' => $yayasan->id,
            'yayasan_nama' => $yayasan->nama,
            'lembaga' => $rincianLembaga,
            'total_siswa' => array_sum(array_column($rincianLembaga, 'jumlah_siswa')),
            'total' => $total,
        ];
    }
}
