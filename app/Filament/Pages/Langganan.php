<?php

namespace App\Filament\Pages;

use App\Models\ModulePrice;
use App\Models\PlatformBroadcast;
use App\Services\TenantBillingCalculator;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

/**
 * Halaman "Langganan" milik tenant sendiri -- PENGGANTI halaman
 * publik lama (resources/views/public/langganan.blade.php, TIDAK
 * dihapus fisik tapi tidak lagi dipakai/di-link) dan RelationManager
 * "Modul Aktif" yang sebelumnya nempel di halaman Edit Lembaga
 * (sudah dicopot dari sana, lihat LembagaResource::getRelations()).
 *
 * Sengaja jadi Filament Page (bukan blade route terpisah) supaya
 * konsisten dengan panel — dan supaya BISA dijadikan satu-satunya
 * menu yang selalu terlihat tanpa perlu hasFeature(), lewat
 * canAccess() yang selalu true untuk user Yayasan.
 *
 * TIDAK ADA lagi "pilih paket" (Profesional/Akses Platform/Paket
 * Full) di sini -- paket/harga akhir SEPENUHNYA ditentukan dari modul
 * mana yang diaktifkan (keputusan revisi terbaru). Plan "Akses
 * Platform" tetap ada di database sebagai basis biaya dasar, tapi
 * dibuat OTOMATIS saat daftar (lihat PublicRegistrationController),
 * tidak lagi dipilih manual oleh tenant.
 *
 * $billingCycle (baru): toggle Bulanan/Tahunan yang BENERAN
 * fungsional -- menentukan siklus_billing pada Subscription yang
 * dibuat lewat bayarSekarang()/aktifkanPaketFull(), bukan cuma
 * tampilan. Nilai ini TIDAK disimpan permanen di database sampai
 * tenant benar-benar menekan salah satu tombol bayar/aktifkan --
 * sebelum itu murni preferensi tampilan di sesi ini.
 */
class Langganan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Langganan';
    protected static ?string $title = 'Langganan Saya';
    protected static ?int $navigationSort = -20;
    protected static ?string $slug = 'langganan';

    protected static string $view = 'filament.pages.langganan';

    public string $billingCycle = 'bulanan';

    // Kalau ada pembayaran yang MASIH BISA dilanjutkan (dibuat < 55
    // menit lalu -- DOKU checkout kadaluarsa 60 menit, lihat
    // payment_due_date di DokuService::buatPaymentRequest()), URL-nya
    // ditaruh di sini supaya tenant bisa lanjutkan pembayaran yang
    // sama, BUKAN bikin transaksi baru tiap klik ulang (sebelumnya
    // properti ini ada di blade tapi tidak pernah benar-benar diisi
    // -- ditemukan 5 Sep 2026 waktu ada 2 baris "Paket Full -
    // Menunggu Pembayaran" kembar gara-gara diklik 2x).
    public ?string $pendingUrl = null;

    // Cache in-memory SEKALI PER REQUEST -- bukan cache permanen/lintas
    // request (properti protected TIDAK di-serialize Livewire antar
    // request, jadi otomatis "kosong lagi" tiap kali halaman di-load
    // ulang -- tidak ada risiko data basi). Tujuannya cuma mencegah
    // getLembagas()/getModulOptions() query database BERKALI-KALI
    // dalam satu render yang sama -- sebelum ini, isModuleActive()
    // (dipanggil per sel di tabel matrix modul x lembaga, bisa puluhan
    // kali sekali render) memanggil getLembagas() SETIAP KALI, dan
    // getLembagas() SELALU query baru -- ditemukan sebagai penyebab 1
    // render halaman ini sempat menghasilkan 121 query / 929 model
    // (7 Sep 2026, waktu debug soal RAM VPS yang mepet).
    protected ?\Illuminate\Support\Collection $lembagasCache = null;

    protected ?\Illuminate\Support\Collection $modulOptionsCache = null;

    public function mount(): void
    {
        // Toggle mulai dari siklus yang BENAR-BENAR sedang aktif (kalau
        // ada) -- supaya tidak kelihatan "salah sinkron" kayak sebelumnya
        // (toggle selalu nunjukin Bulanan padahal langganan aktifnya
        // Tahunan).
        $this->billingCycle = $this->getSubscriptionAktif()?->siklus_billing ?? 'bulanan';

        $this->pendingUrl = $this->cariPendingUrlPembayaran();
    }

    /**
     * Cari URL checkout DOKU dari pembayaran 'pending' PALING BARU yang
     * masih dalam jendela waktu berlaku (< 55 menit sejak dibuat) --
     * kalau ketemu, tenant tinggal lanjutkan pembayaran yang sama lewat
     * link ini, TIDAK perlu (dan tidak boleh) bikin transaksi baru.
     * Pembayaran yang sudah lewat jendela waktu dianggap kadaluarsa --
     * biarkan tenant mulai transaksi baru kalau masih mau bayar.
     *
     * @param  ?int  $planId  Kalau diisi, HARUS plan yang sama dengan
     *         subscription pending itu -- supaya pending lama Paket A
     *         tidak "ketiban" dipakai balik waktu tenant sebenarnya mau
     *         bayar Paket B (atau siklus beda). Kosongkan cuma untuk
     *         cek umum "ada pending apa saja" (dipakai banner info di
     *         atas halaman).
     */
    protected function cariPendingUrlPembayaran(?int $planId = null, ?string $siklus = null): ?string
    {
        $query = $this->getYayasan()->subscriptions()
            ->where('status', 'pending');

        if ($planId) {
            $query->where('subscription_plan_id', $planId);
        }

        if ($siklus) {
            $query->where('siklus_billing', $siklus);
        }

        $subscription = $query->latest()->first();

        if (! $subscription) {
            return null;
        }

        $payment = $subscription->payments()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (! $payment || ! $payment->created_at || $payment->created_at->diffInMinutes(now()) >= 55) {
            return null;
        }

        return $payment->gateway_raw_response['response']['payment']['url'] ?? null;
    }

    public static function canAccess(): bool
    {
        // Platform admin tidak perlu halaman ini (urusan billing per
        // Yayasan dikelola dari Panel Platform). Untuk user Yayasan,
        // SELALU bisa akses -- ini pengecualian dari hasFeature(),
        // supaya tetap terlihat walau belum ada modul apapun aktif.
        if (auth()->user()?->is_platform_admin) {
            return false;
        }

        return (bool) auth()->user()?->yayasan_id;
    }

    public function getYayasan()
    {
        return Auth::user()->yayasan;
    }

    /**
     * Tombol "Bayar Sekarang" cuma disembunyikan kalau yayasan ini
     * SUDAH PERNAH bayar sungguhan (status Yayasan sudah 'active' --
     * BUKAN cuma subscription trial placeholder yang statusnya juga
     * 'active') DAN belum mendekati jatuh tempo. Selama masih trial
     * atau sudah suspended, tombol WAJIB tetap muncul supaya tenant
     * bisa aktivasi/bayar kapan saja. Begitu masuk jendela H-7 sebelum
     * jatuh tempo (konsisten dengan reminder WA H-7/H-3/H-1 yang sudah
     * ada), tombol muncul lagi untuk perpanjangan.
     */
    public function shouldShowBayarButton(): bool
    {
        $yayasan = $this->getYayasan();

        if ($yayasan->status !== 'active') {
            // trial atau suspended -- selalu boleh bayar/aktivasi.
            return true;
        }

        $subAktif = $this->getSubscriptionAktif();

        if (! $subAktif || ! $subAktif->berakhir_pada) {
            return true;
        }

        $sisaHari = ($subAktif->berakhir_pada->timestamp - now()->timestamp) / 86400;

        return $sisaHari <= 7;
    }

    public function setBillingCycle(string $cycle): void
    {
        $this->billingCycle = in_array($cycle, ['bulanan', 'tahunan'], true) ? $cycle : 'bulanan';
    }

    public function isTahunanDipilih(): bool
    {
        return $this->billingCycle === 'tahunan';
    }

    /**
     * True kalau toggle yang sedang dipilih BEDA dari siklus_billing
     * langganan yang benar-benar aktif -- dipakai buat munculkan banner
     * penjelasan "ini cuma pratinjau, belum ada tagihan sekarang".
     */
    public function isPreviewBerbedaDariAktif(): bool
    {
        $aktif = $this->getSubscriptionAktif();

        return $aktif && $aktif->status === 'active' && $aktif->siklus_billing !== $this->billingCycle;
    }

    /**
     * Estimasi yang ditampilkan mengikuti toggle $billingCycle --
     * SELALU dihitung ulang dari TenantBillingCalculator (harga
     * modul/plan/diskon tahunan TERKINI), tidak pernah angka lama
     * yang dibekukan.
     */
    public function getEstimasi(): array
    {
        $calculator = app(TenantBillingCalculator::class);
        $yayasan = $this->getYayasan();

        return $this->isTahunanDipilih()
            ? $calculator->hitungYayasanTahunan($yayasan)
            : $calculator->hitungYayasan($yayasan);
    }

    public function getModulOptions()
    {
        return $this->modulOptionsCache ??= ModulePrice::aktif()->orderBy('urutan')->get();
    }

    public function getLembagas()
    {
        return $this->lembagasCache ??= $this->getYayasan()->lembagas()
            ->with(['modules.modulePrice'])
            ->get();
    }

    public function isModuleActive(int $lembagaId, int $modulePriceId): bool
    {
        foreach ($this->getLembagas() as $lembaga) {
            if ($lembaga->id !== $lembagaId) {
                continue;
            }

            foreach ($lembaga->modules as $lm) {
                if ($lm->module_price_id === $modulePriceId && $lm->is_active) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Toggle 1 modul untuk 1 Lembaga -- dipanggil langsung dari
     * checkbox lewat wire:click. TIDAK memicu pembayaran/redirect
     * apapun saat itu juga: modul yang aktif otomatis masuk hitungan
     * TenantBillingCalculator (jadi langsung terlihat di estimasi),
     * dan otomatis masuk tagihan bulan berikutnya lewat
     * subscription:generate-monthly-invoice (yang sudah ada) --
     * konsisten dengan prinsip "aktifkan dulu, tagihan otomatis
     * menyusul" yang sudah kita putuskan sebelumnya.
     */
    public function toggleModule(int $lembagaId, int $modulePriceId): void
    {
        $lembaga = $this->getYayasan()->lembagas()->findOrFail($lembagaId);

        $existing = $lembaga->modules()->where('module_price_id', $modulePriceId)->first();

        if ($existing && $existing->is_active) {
            $existing->update(['is_active' => false, 'nonaktif_sejak' => now()]);

            Notification::make()->title('Modul dinonaktifkan')->success()->send();
        } elseif ($existing) {
            $existing->update(['is_active' => true, 'aktif_sejak' => now(), 'nonaktif_sejak' => null]);

            Notification::make()->title('Modul diaktifkan')->body('Otomatis masuk tagihan bulan berikutnya.')->success()->send();
        } else {
            $lembaga->modules()->create([
                'module_price_id' => $modulePriceId,
                'is_active' => true,
                'aktif_sejak' => now(),
            ]);

            Notification::make()->title('Modul diaktifkan')->body('Otomatis masuk tagihan bulan berikutnya.')->success()->send();
        }
    }

    public function getSubscriptionAktif()
    {
        return $this->getYayasan()->activeSubscription();
    }

    public function getRiwayatPembayaran()
    {
        return $this->getYayasan()->subscriptions()
            ->with(['plan', 'payments'])
            ->latest()
            ->get();
    }


    /**
     * Bayar sesuai $billingCycle yang sedang dipilih tenant --
     * computed_amount/computed_breakdown dihitung lewat calculator
     * yang sesuai (bulanan atau tahunan), siklus_billing tersimpan di
     * baris Subscription yang baru supaya command autopilot bulanan/
     * tahunan tahu harus memperlakukan Yayasan ini seperti apa mulai
     * sekarang.
     */
    public function bayarSekarang(): void
    {
        $yayasan = $this->getYayasan();
        $plan = \App\Models\SubscriptionPlan::where('slug', 'akses-platform')->firstOrFail();
        $tahunan = $this->isTahunanDipilih();

        // Cek pending yang cocok PLAN & SIKLUS ini SPESIFIK -- bukan
        // $this->pendingUrl (dihitung sekali waktu halaman dibuka,
        // sebelum tenant sempat toggle Bulanan/Tahunan atau ganti
        // pilihan modul). Kalau dipakai apa adanya, tenant yang sempat
        // klik bayar Bulanan lalu ganti pikiran ke Tahunan bisa
        // "terjebak" diarahkan balik ke pending Bulanan yang lama.
        $pendingUrlCocok = $this->cariPendingUrlPembayaran($plan->id, $tahunan ? 'tahunan' : 'bulanan');

        if ($pendingUrlCocok) {
            $this->redirect($pendingUrlCocok);

            return;
        }

        $calculator = app(TenantBillingCalculator::class);

        $hasil = $tahunan
            ? $calculator->hitungYayasanTahunan($yayasan)
            : $calculator->hitungYayasan($yayasan);

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'siklus_billing' => $tahunan ? 'tahunan' : 'bulanan',
            'status' => 'pending',
            'computed_amount' => $hasil['total'],
            'computed_breakdown' => $hasil,
            'periode' => $tahunan ? (string) now()->addYear()->year : now()->format('Y-m'),
        ]);

        if (($hasil['promo_pendaftaran_persen'] ?? 0) > 0) {
            $yayasan->update(['promo_pendaftaran_terpakai' => true]);

            try {
                \App\Services\NotificationService::sendBroadcastYayasan(
                    $yayasan,
                    'Diskon Pendaftaran Diterapkan',
                    "Tagihan ini sudah termasuk diskon pendaftaran \"{$hasil['promo_pendaftaran_teks']}\" ({$hasil['promo_pendaftaran_persen']}%). " .
                    'Diskon ini berlaku SATU KALI untuk tagihan ini saja -- tagihan berikutnya akan kembali ke harga normal.'
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Langganan::bayarSekarang: gagal kirim notif penjelasan promo untuk yayasan {$yayasan->id}: {$e->getMessage()}");
            }
        }

        $this->buatTransaksiDokuDanRedirect($yayasan, $subscription, $plan, $hasil['total'], $tahunan);
    }

    /**
     * Buat transaksi pembayaran DOKU untuk 1 Subscription 'pending' &
     * redirect tenant ke halaman checkout DOKU -- dipakai bersama oleh
     * bayarSekarang() & aktifkanPaketFull() supaya kedua alur konsisten
     * (dan tidak dobel kode).
     *
     * channel: 'ALL' (bukan salah satu channel spesifik seperti 'QRIS')
     * -- DokuService::paymentMethodTypes() balikin array KOSONG untuk
     * channel yang tidak dikenali, dan array kosong artinya DOKU
     * tampilkan SEMUA metode pembayaran aktif di halaman checkout-nya
     * (VA semua bank, QRIS, e-wallet, dll), bukan cuma 1 metode
     * spesifik -- sesuai keputusan Anda.
     */
    protected function buatTransaksiDokuDanRedirect(
        \App\Models\Yayasan $yayasan,
        \App\Models\Subscription $subscription,
        \App\Models\SubscriptionPlan $plan,
        int $amount,
        bool $tahunan
    ): void {
        if (blank(config('services.doku.client_id'))) {
            Notification::make()
                ->title('Pembayaran otomatis belum diaktifkan')
                ->body('Silakan hubungi admin Qinara untuk pembayaran manual.')
                ->danger()
                ->send();

            return;
        }

        $referenceId = 'SUB-' . $subscription->id . '-' . time();

        $doku = app(\App\Services\DokuService::class);

        try {
            $result = $doku->buatPaymentRequest(
                referenceId: $referenceId,
                amount: $amount,
                customerName: $yayasan->nama,
                customerEmail: $yayasan->email ?? Auth::user()->email,
                judul: 'Langganan ' . $plan->nama . ' (' . ($tahunan ? '1 tahun' : '1 bulan') . ') -- ' . $yayasan->nama,
                channel: 'ALL'
            );
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal membuat transaksi pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        // Path field URL sesuai dokumentasi resmi DOKU Checkout:
        // response.payment.url (lihat juga catatan yang sama di
        // SubscriptionController::payDoku()).
        $paymentUrl = $result['response']['payment']['url'] ?? null;

        if (! $paymentUrl) {
            Notification::make()
                ->title('Gagal membuat transaksi pembayaran')
                ->body('URL pembayaran tidak ditemukan di respons DOKU.')
                ->danger()
                ->send();

            return;
        }

        $subscription->payments()->create([
            'jumlah' => $amount,
            'metode' => 'doku',
            'status' => 'pending',
            'gateway_order_id' => $referenceId,
            'gateway_raw_response' => $result,
        ]);

        $this->redirect($paymentUrl);
    }

    /**
     * Shortcut "Aktifkan Paket Full" -- pindahkan Subscription aktif
     * ke plan 'paket-full' (termasuk_semua_modul=true, supaya
     * TenantBillingCalculator tidak menghitung modul dobel di atas
     * harga flat-nya) DAN aktifkan semua modul di semua Lembaga milik
     * yayasan ini, supaya tercatat & terlihat "termasuk" di rincian.
     * Siklus billing (bulanan/tahunan) ikut $billingCycle yang sedang
     * dipilih tenant, sama seperti bayarSekarang().
     *
     * Sebelum memaksa semua modul aktif, pilihan modul yang SEDANG
     * berjalan (per Lembaga) di-snapshot dulu ke
     * Yayasan::modul_snapshot_sebelum_full -- supaya kalau tenant
     * nanti klik "Kembali Pilih Satu-satu" (batalkanPaketFull),
     * pilihannya bisa dikembalikan persis seperti semula, bukan
     * dibiarkan semua modul tetap aktif begitu saja (yang bisa bikin
     * sekolah salah kira modul itu memang mereka pilih).
     */
    /**
     * "Aktifkan Paket Full" -- SEBELUMNYA method ini langsung
     * mengaktifkan Paket Full & menyalakan semua modul TANPA lewat
     * pembayaran sama sekali (celah billing ditemukan 4 Sep 2026).
     * Sekarang alurnya disamakan persis dengan bayarSekarang(): bikin
     * Subscription berstatus 'pending', hitung tagihan KHUSUS untuk
     * plan Paket Full (bukan plan yang sedang aktif sekarang -- lihat
     * parameter $planOverride di TenantBillingCalculator), lalu
     * redirect ke payment gateway. Modul di tiap Lembaga BARU dinyalakan
     * oleh webhook (XenditWebhookController::handleSubscriptionInvoice)
     * setelah pembayaran BENAR-BENAR sukses -- bukan di sini lagi.
     */
    public function aktifkanPaketFull(): void
    {
        $yayasan = $this->getYayasan();
        $planFull = \App\Models\SubscriptionPlan::where('slug', 'paket-full')->first();
        $tahunan = $this->isTahunanDipilih();

        if (! $planFull) {
            Notification::make()->title('Paket Full belum tersedia')->danger()->send();

            return;
        }

        // Sama seperti bayarSekarang() -- cek pending yang cocok PLAN &
        // SIKLUS ini spesifik, bukan pending apa saja, supaya tenant
        // yang ganti pikiran (mis. dari Bulanan ke Tahunan) tidak
        // "terjebak" diarahkan balik ke pending lama yang beda siklus.
        $pendingUrlCocok = $this->cariPendingUrlPembayaran($planFull->id, $tahunan ? 'tahunan' : 'bulanan');

        if ($pendingUrlCocok) {
            $this->redirect($pendingUrlCocok);

            return;
        }

        $calculator = app(TenantBillingCalculator::class);

        $hasil = $tahunan
            ? $calculator->hitungYayasanTahunan($yayasan, $planFull)
            : $calculator->hitungYayasan($yayasan, $planFull);

        // Snapshot modul yang sedang berjalan SEBELUM pindah ke Paket
        // Full -- supaya kalau nanti tenant klik "Kembali Pilih
        // Satu-satu" (batalkanPaketFull), pilihannya bisa dikembalikan
        // persis seperti semula. Cuma diambil kalau BELUM sedang Paket
        // Full, supaya klik dua kali beruntun tidak menimpa snapshot
        // lama dengan kondisi "semua aktif" (yang percuma).
        if (! $this->isPaketFullAktif()) {
            $snapshot = [];

            foreach ($this->getLembagas() as $lembaga) {
                $snapshot[$lembaga->id] = $lembaga->modules
                    ->where('is_active', true)
                    ->pluck('module_price_id')
                    ->values()
                    ->all();
            }

            $yayasan->update(['modul_snapshot_sebelum_full' => $snapshot]);
        }

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $planFull->id,
            'siklus_billing' => $tahunan ? 'tahunan' : 'bulanan',
            'status' => 'pending',
            'computed_amount' => $hasil['total'],
            'computed_breakdown' => $hasil,
            'periode' => $tahunan ? (string) now()->addYear()->year : now()->format('Y-m'),
        ]);

        if (($hasil['promo_pendaftaran_persen'] ?? 0) > 0) {
            $yayasan->update(['promo_pendaftaran_terpakai' => true]);

            try {
                \App\Services\NotificationService::sendBroadcastYayasan(
                    $yayasan,
                    'Diskon Pendaftaran Diterapkan',
                    "Tagihan ini sudah termasuk diskon pendaftaran \"{$hasil['promo_pendaftaran_teks']}\" ({$hasil['promo_pendaftaran_persen']}%). " .
                    'Diskon ini berlaku SATU KALI untuk tagihan ini saja -- tagihan berikutnya akan kembali ke harga normal.'
                );
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Langganan::aktifkanPaketFull: gagal kirim notif penjelasan promo untuk yayasan {$yayasan->id}: {$e->getMessage()}");
            }
        }

        try {
            $this->buatTransaksiDokuDanRedirect($yayasan, $subscription, $planFull, $hasil['total'], $tahunan);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal membuat transaksi pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function isPaketFullAktif(): bool
    {
        return (bool) $this->getSubscriptionAktif()?->plan?->termasuk_semua_modul;
    }

    /**
     * Kebalikan dari aktifkanPaketFull() -- pindah balik ke plan
     * 'akses-platform', lalu KEMBALIKAN modul per Lembaga persis
     * seperti sebelum Paket Full diaktifkan (dari
     * modul_snapshot_sebelum_full). Kalau ternyata tidak ada snapshot
     * tersimpan (misal data lama dari sebelum fitur ini ada), modul
     * dibiarkan seperti apa adanya sekarang -- tidak dipaksa mati
     * semua, supaya tidak tiba-tiba menghilangkan modul yang memang
     * mau tetap dipakai tenant.
     */
    public function batalkanPaketFull(): void
    {
        $yayasan = $this->getYayasan();
        $planDasar = \App\Models\SubscriptionPlan::where('slug', 'akses-platform')->firstOrFail();

        $subAktif = $yayasan->activeSubscription();

        if ($subAktif) {
            $subAktif->update(['subscription_plan_id' => $planDasar->id]);
        }

        $snapshot = $yayasan->modul_snapshot_sebelum_full;

        if (is_array($snapshot)) {
            foreach ($this->getLembagas() as $lembaga) {
                $modulIdSebelumnya = $snapshot[$lembaga->id] ?? [];

                foreach ($lembaga->modules as $lm) {
                    $harusAktif = in_array($lm->module_price_id, $modulIdSebelumnya, true);

                    if ($lm->is_active !== $harusAktif) {
                        $lm->update([
                            'is_active' => $harusAktif,
                            'nonaktif_sejak' => $harusAktif ? null : now(),
                        ]);
                    }
                }
            }

            $yayasan->update(['modul_snapshot_sebelum_full' => null]);
        }

        Notification::make()
            ->title('Paket Full dibatalkan')
            ->body('Modul dikembalikan seperti pilihan Anda sebelumnya, dihitung per modul yang dicentang.')
            ->success()
            ->send();
    }

    public function getBroadcasts()
    {
        return PlatformBroadcast::where('status', '!=', 'draft')
            ->latest('dikirim_pada')
            ->get()
            ->filter(fn ($b) => $b->includesYayasan($this->getYayasan()))
            ->take(10);
    }
}
