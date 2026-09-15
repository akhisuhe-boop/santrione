<?php

namespace App\Services;

use App\Models\DiskonVolumeSiswa;
use App\Models\Lembaga;
use App\Models\ModulePrice;
use App\Models\SubscriptionPlan;
use App\Models\Yayasan;

/**
 * Satu-satunya tempat rumus tagihan dihitung.
 *
 * DIUBAH TOTAL (12 Sep 2026) -- skema harga per-Lembaga (kuota siswa
 * per Lembaga, diskon bertingkat per-urutan-Lembaga) diganti skema
 * per-YAYASAN yang jauh lebih sederhana, sesuai diskusi & ACC:
 *
 *   rate per siswa = harga_dasar_per_siswa (SELALU, berapa pun modul
 *                     aktif -- pengganti pendapatan modul gratis)
 *                   + jumlah(harga_per_siswa tiap modul BERBAYAR yang
 *                     aktif untuk Yayasan ini)
 *
 *   subtotal        = rate per siswa x TOTAL siswa se-Yayasan (semua
 *                      Lembaga digabung, bukan dihitung per-Lembaga
 *                      lalu dijumlah lagi)
 *   - diskon volume (tabel DiskonVolumeSiswa, berdasar total siswa)
 *   - diskon Paket Full (KHUSUS kalau plan-nya Paket Full, di atas
 *     diskon volume)
 *   - diskon tahunan / promo pendaftaran (LOGIKANYA TIDAK BERUBAH,
 *     tetap saling eksklusif seperti sebelumnya -- lihat
 *     hitungYayasanTahunan())
 *
 * PENTING -- modul TIDAK dihitung per-Lembaga lagi. Aktivasi modul
 * disinkronkan ke SEMUA Lembaga se-Yayasan sekaligus (lihat
 * Checkout::toggleModule() dan LembagaObserver), jadi "modul aktif
 * untuk Yayasan" cukup dicek dari LEMBAGA MANA SAJA milik Yayasan itu
 * (asumsikan semuanya tersinkron). Tabel/relasi `lembaga_modules`
 * SENGAJA TIDAK DIUBAH STRUKTURNYA -- supaya 20+ tempat lain yang cek
 * modul per-Lembaga (Jurnal Mengajar, Laporan Tahfidz, dst) tetap
 * jalan tanpa disentuh.
 *
 * Tidak ada Yayasan yang bayar dengan skema lama saat perubahan ini
 * dibuat, jadi tidak ada migrasi data/harga yang perlu dijaga
 * kompatibel ke belakang.
 */
class TenantBillingCalculator
{
    protected function planAksesPlatform(): ?SubscriptionPlan
    {
        return SubscriptionPlan::where('slug', 'akses-platform')->first();
    }

    protected function planPaketFull(): ?SubscriptionPlan
    {
        return SubscriptionPlan::where('slug', 'paket-full')->first();
    }

    /**
     * Total siswa SE-YAYASAN (semua Lembaga digabung) -- ini pengali
     * tunggal di skema baru, ganti "jumlah siswa per Lembaga" yang
     * dulu dihitung terpisah tiap Lembaga.
     *
     * DIOPTIMASI (15 Sep 2026) -- versi sebelumnya panggil
     * jumlahSiswaAktif() di dalam loop, yang menjalankan 1 query
     * COUNT terpisah PER LEMBAGA (N+1). Untuk Yayasan dengan banyak
     * Lembaga, itu bisa jadi puluhan query cuma buat 1 halaman
     * Checkout. Sekarang: kumpulkan dulu Lembaga mana yang punya
     * jumlah_siswa_billing manual, sisanya (yang belum diisi manual)
     * dihitung SEKALIGUS lewat 1 query gabungan -- jadi maksimal 2
     * query total, bukan 1+N.
     */
    public function totalSiswaYayasan(Yayasan $yayasan): int
    {
        $lembagas = $yayasan->lembagas()->get(['id', 'jumlah_siswa_billing']);

        $total = 0;
        $idPerluHitung = [];

        foreach ($lembagas as $l) {
            if ($l->jumlah_siswa_billing !== null) {
                $total += (int) $l->jumlah_siswa_billing;
            } else {
                $idPerluHitung[] = $l->id;
            }
        }

        if (! empty($idPerluHitung)) {
            $total += \App\Models\Siswa::whereIn('lembaga_id', $idPerluHitung)
                ->where('status_siswa', 'Aktif')
                ->count();
        }

        return $total;
    }

    /**
     * Modul BERBAYAR yang aktif untuk Yayasan ini -- dicek dari
     * Lembaga MANA SAJA milik Yayasan (aktivasi disinkronkan ke semua
     * Lembaga, lihat catatan class di atas). $paksaSemuaAktif dipakai
     * untuk preview Paket Full (semua modul berbayar dianggap aktif,
     * walau belum benar-benar disimpan).
     *
     * @return \Illuminate\Support\Collection<int, ModulePrice>
     */
    protected function modulBerbayarAktif(Yayasan $yayasan, bool $paksaSemuaAktif = false): \Illuminate\Support\Collection
    {
        $semuaModulBerbayar = ModulePrice::aktif()->where('is_gratis', false)->orderBy('urutan')->get();

        if ($paksaSemuaAktif) {
            return $semuaModulBerbayar;
        }

        $lembagaPertama = $yayasan->lembagas()->with('modules')->first();

        if (! $lembagaPertama) {
            return collect();
        }

        $idAktif = $lembagaPertama->modules->where('is_active', true)->pluck('module_price_id');

        return $semuaModulBerbayar->whereIn('id', $idAktif)->values();
    }

    /**
     * Inti rumus (dipakai BERSAMA oleh hitungYayasanMurni() -- Yayasan
     * asli, dari Checkout -- dan hitungEstimasiPublik() -- angka
     * mentah, dari kalkulator landing page publik). SATU rumus, DUA
     * pemakai, supaya angka yang tenant lihat di landing page
     * DIJAMIN sama persis dengan yang muncul di Checkout untuk
     * kombinasi siswa+modul yang sama -- tidak ada resiko dua rumus
     * ini perlahan jadi beda kalau nanti diubah.
     *
     * @param  \Illuminate\Support\Collection<int, ModulePrice>  $modulAktifBerbayar
     */
    protected function hitungInti(int $totalSiswa, \Illuminate\Support\Collection $modulAktifBerbayar, ?SubscriptionPlan $planOverride, bool $iniPaketFull): array
    {
        $planDasar = $this->planAksesPlatform();
        $hargaDasarPerSiswa = (int) ($planDasar->harga_dasar_per_siswa ?? 0);

        $rincianModul = $modulAktifBerbayar->map(fn (ModulePrice $mp) => [
            'key' => $mp->key,
            'nama' => $mp->nama,
            'harga_per_siswa' => $mp->hargaTagihSekolah(),
        ])->values()->all();

        $totalRateModul = array_sum(array_column($rincianModul, 'harga_per_siswa'));
        $ratePerSiswa = $hargaDasarPerSiswa + $totalRateModul;

        $subtotalSebelumDiskon = $ratePerSiswa * $totalSiswa;

        $diskonVolumePersen = DiskonVolumeSiswa::persenUntuk($totalSiswa);
        $setelahDiskonVolume = (int) round($subtotalSebelumDiskon * (100 - $diskonVolumePersen) / 100);

        $diskonPaketFullPersen = $iniPaketFull ? (int) ($planOverride->diskon_paket_full_persen ?? 0) : 0;
        $total = $diskonPaketFullPersen > 0
            ? (int) round($setelahDiskonVolume * (100 - $diskonPaketFullPersen) / 100)
            : $setelahDiskonVolume;

        $modulGratis = ModulePrice::aktif()->where('is_gratis', true)->orderBy('urutan')->get()
            ->map(fn (ModulePrice $mp) => [
                'key' => $mp->key,
                'nama' => $mp->nama,
                'harga_per_siswa' => 0,
            ])->values()->all();

        return [
            'total_siswa' => $totalSiswa,
            'is_paket_full' => $iniPaketFull,

            'harga_dasar_per_siswa' => $hargaDasarPerSiswa,
            'rincian_modul' => $rincianModul,
            'rincian_modul_gratis' => $modulGratis,
            'rate_per_siswa' => $ratePerSiswa,

            'subtotal_sebelum_diskon' => $subtotalSebelumDiskon,
            'diskon_volume_persen' => $diskonVolumePersen,
            'setelah_diskon_volume' => $setelahDiskonVolume,
            'diskon_paket_full_persen' => $diskonPaketFullPersen,

            'total' => $total,
        ];
    }

    /**
     * Hitung tagihan BULANAN "murni" (belum ada diskon tahunan/promo)
     * -- angka dasar yang dipakai ulang oleh hitungYayasan() dan
     * hitungYayasanTahunan(), supaya keduanya selalu mulai dari titik
     * yang sama persis.
     *
     * $planOverride diisi SubscriptionPlan Paket Full untuk preview
     * "kalau ambil Paket Full, berapa tagihannya" SEBELUM benar-benar
     * dipilih.
     */
    protected function hitungYayasanMurni(Yayasan $yayasan, ?SubscriptionPlan $planOverride = null): array
    {
        $iniPaketFull = (bool) ($planOverride?->termasuk_semua_modul ?? false);
        $modulAktif = $this->modulBerbayarAktif($yayasan, paksaSemuaAktif: $iniPaketFull);
        $totalSiswa = $this->totalSiswaYayasan($yayasan);

        $hasil = $this->hitungInti($totalSiswa, $modulAktif, $planOverride, $iniPaketFull);

        return array_merge($hasil, [
            'yayasan_id' => $yayasan->id,
            'yayasan_nama' => $yayasan->nama,
        ]);
    }

    /**
     * DITAMBAHKAN -- versi PUBLIK (kalkulator interaktif di landing
     * page), TANPA butuh akun/Yayasan sama sekali. Terima angka
     * mentah dari pengunjung: total siswa yang mereka ketik, dan
     * key modul mana saja yang mereka centang. Selalu bulanan --
     * konversi ke tahunan (kalau perlu) dilakukan di sisi tampilan
     * (JS), sama seperti kartu harga yang sudah ada, BUKAN dihitung
     * ulang di sini -- supaya satu response bisa dipakai buat kedua
     * mode tanpa panggil endpoint 2x.
     *
     * $paketFull true = anggap SEMUA modul berbayar aktif (parameter
     * $modulKeys diabaikan), diskon Paket Full ikut diterapkan.
     */
    public function hitungEstimasiPublik(int $totalSiswa, array $modulKeys = [], bool $paketFull = false): array
    {
        $totalSiswa = max(0, $totalSiswa);

        $semuaModulBerbayar = ModulePrice::aktif()->where('is_gratis', false)->orderBy('urutan')->get();

        $modulAktif = $paketFull
            ? $semuaModulBerbayar
            : $semuaModulBerbayar->whereIn('key', $modulKeys)->values();

        $planFull = $paketFull ? $this->planPaketFull() : null;

        return $this->hitungInti($totalSiswa, $modulAktif, $planFull, $paketFull);
    }

    /**
     * Hitung tagihan BULANAN gabungan. Kalau Yayasan ini punya promo
     * pendaftaran yang belum dipakai, diterapkan di sini (satu kali,
     * ke tagihan bulanan berikutnya) -- LOGIKA TIDAK BERUBAH dari
     * sebelumnya.
     */
    public function hitungYayasan(Yayasan $yayasan, ?SubscriptionPlan $planOverride = null): array
    {
        $murni = $this->hitungYayasanMurni($yayasan, $planOverride);

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
     * Hitung tagihan TAHUNAN gabungan. Selalu turunan dari angka
     * bulanan MURNI x 12, baru salah SATU dari dua hal berikut
     * diterapkan -- TIDAK PERNAH DUA-DUANYA SEKALIGUS (LOGIKA TIDAK
     * BERUBAH dari sebelumnya):
     *
     *  - Promo pendaftaran belum dipakai -> promo itu yang berlaku.
     *  - Kalau tidak ada promo -> diskon_tahunan_persen plan yang
     *    berlaku seperti biasa.
     */
    public function hitungYayasanTahunan(Yayasan $yayasan, ?SubscriptionPlan $planOverride = null): array
    {
        $murni = $this->hitungYayasanMurni($yayasan, $planOverride);
        $plan = $planOverride ?? $this->planAksesPlatform();

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
