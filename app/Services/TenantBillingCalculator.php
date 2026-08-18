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
 *
 * PROMO PENDAFTARAN (baru): kalau Yayasan pernah daftar saat promo
 * landing page aktif (snapshot di Yayasan->promo_pendaftaran_*, lihat
 * PublicRegistrationController) dan BELUM PERNAH dipakai, promo itu
 * MENANG atas diskon tahunan untuk TEPAT SATU tagihan berikutnya
 * (siapa pun caller-nya -- generate-monthly-invoice, generate-annual-
 * invoice, atau bayarSekarang() manual). Method di sini HANYA
 * menghitung apakah promo itu akan berlaku -- yang MENANDAI promo
 * sebagai "terpakai" adalah tanggung jawab kode yang benar-benar
 * membuat baris Subscription (bukan di sini), supaya method hitung di
 * class ini tetap murni/tidak override) dan aman dipanggil berkali-kali
 * cuma untuk preview (halaman Langganan) tanpa efek samping.
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
     * Hitung tagihan gabungan seluruh Lembaga dalam 1 Yayasan, TANPA
     * diskon tahunan/promo apa pun -- angka dasar "murni" yang dipakai
     * ulang oleh hitungYayasan() dan hitungYayasanTahunan() di bawah,
     * supaya kedua-duanya selalu mulai dari titik yang sama persis.
     */
    protected function hitungYayasanMurni(Yayasan $yayasan): array
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

    /**
     * Hitung tagihan BULANAN gabungan seluruh Lembaga. Kalau Yayasan
     * ini punya promo pendaftaran yang belum dipakai, diterapkan di
     * sini (satu kali, ke tagihan bulanan berikutnya).
     */
    public function hitungYayasan(Yayasan $yayasan): array
    {
        $murni = $this->hitungYayasanMurni($yayasan);

        $promoPersen = $yayasan->promoPendaftaranBelumDipakai() ? (int) $yayasan->promo_pendaftaran_persen : 0;
        $total = $promoPersen > 0
            ? (int) round($murni['total'] * (100 - $promoPersen) / 100)
            : $murni['total'];

        return array_merge($murni, [
            'total' => $total,
            'subtotal_sebelum_promo' => $murni['total'],
            'promo_pendaftaran_persen' => $promoPersen,
            'promo_pendaftaran_teks' => $promoPersen > 0 ? $yayasan->promo_pendaftaran_teks : null,
        ]);
    }

    /**
     * Hitung tagihan TAHUNAN gabungan seluruh Lembaga dalam 1 Yayasan.
     * Selalu turunan dari angka bulanan MURNI (belum ada diskon apa
     * pun) x 12, baru salah SATU dari dua hal berikut diterapkan --
     * TIDAK PERNAH DUA-DUANYA SEKALIGUS, supaya tidak ada diskon
     * menumpuk di luar kendali:
     *
     *  - Kalau ada promo pendaftaran yang belum dipakai -> promo itu
     *    yang berlaku (diskon_tahunan_persen paket diabaikan untuk
     *    tagihan pertama ini).
     *  - Kalau tidak ada promo -> diskon_tahunan_persen paket yang
     *    berlaku seperti biasa.
     */
    public function hitungYayasanTahunan(Yayasan $yayasan): array
    {
        $murni = $this->hitungYayasanMurni($yayasan);
        $plan = $this->aksesPlatformPlan($yayasan);

        $totalTahunanSebelumDiskon = $murni['total'] * 12;
        $promoPersen = $yayasan->promoPendaftaranBelumDipakai() ? (int) $yayasan->promo_pendaftaran_persen : 0;

        if ($promoPersen > 0) {
            $diskonTahunanPersen = 0;
            $totalTahunanFinal = (int) round($totalTahunanSebelumDiskon * (100 - $promoPersen) / 100);
        } else {
            $diskonTahunanPersen = (int) ($plan->diskon_tahunan_persen ?? 0);
            $totalTahunanFinal = (int) round($totalTahunanSebelumDiskon * (100 - $diskonTahunanPersen) / 100);
        }

        return array_merge($murni, [
            'total_bulanan' => $murni['total'],
            'total_tahunan_sebelum_diskon' => $totalTahunanSebelumDiskon,
            'diskon_tahunan_persen' => $diskonTahunanPersen,
            'promo_pendaftaran_persen' => $promoPersen,
            'promo_pendaftaran_teks' => $promoPersen > 0 ? $yayasan->promo_pendaftaran_teks : null,
            'total' => $totalTahunanFinal,
        ]);
    }
}
