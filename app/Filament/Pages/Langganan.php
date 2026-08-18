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

    public function mount(): void
    {
        // Toggle mulai dari siklus yang BENAR-BENAR sedang aktif (kalau
        // ada) -- supaya tidak kelihatan "salah sinkron" kayak sebelumnya
        // (toggle selalu nunjukin Bulanan padahal langganan aktifnya
        // Tahunan).
        $this->billingCycle = $this->getSubscriptionAktif()?->siklus_billing ?? 'bulanan';
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
        return ModulePrice::aktif()->orderBy('urutan')->get();
    }

    public function getLembagas()
    {
        return $this->getYayasan()->lembagas()
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

    public function getPendingPaymentUrl(): ?string
    {
        $sub = $this->getYayasan()->subscriptions()
            ->where('status', 'pending')
            ->with('payments')
            ->latest()
            ->first();

        $payment = $sub?->payments()->where('status', 'pending')->latest()->first();

        return $payment?->gateway_raw_response['invoice_url']
            ?? $payment?->gateway_raw_response['paymentUrl']
            ?? null;
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
        $calculator = app(TenantBillingCalculator::class);
        $tahunan = $this->isTahunanDipilih();

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

        try {
            $invoiceUrl = app(\App\Services\XenditSubscriptionService::class)
                ->createTransaction($subscription, $plan, $yayasan->email ?? Auth::user()->email);

            $this->redirect($invoiceUrl);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal membuat transaksi pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Shortcut "Aktifkan Paket Full" -- pindahkan Subscription aktif
     * ke plan 'paket-full' (termasuk_semua_modul=true, supaya
     * TenantBillingCalculator tidak menghitung modul dobel di atas
     * harga flat-nya) DAN aktifkan semua modul di semua Lembaga milik
     * yayasan ini, supaya tercatat & terlihat "termasuk" di rincian.
     * Siklus billing (bulanan/tahunan) ikut $billingCycle yang sedang
     * dipilih tenant, sama seperti bayarSekarang().
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

        $subAktif = $yayasan->activeSubscription();

        if ($subAktif) {
            $subAktif->update([
                'subscription_plan_id' => $planFull->id,
                'siklus_billing' => $tahunan ? 'tahunan' : 'bulanan',
            ]);
        } else {
            $yayasan->subscriptions()->create([
                'subscription_plan_id' => $planFull->id,
                'siklus_billing' => $tahunan ? 'tahunan' : 'bulanan',
                'status' => 'active',
                'mulai_pada' => now(),
                'berakhir_pada' => $yayasan->trial_ends_at ?? now()->addDays(config('subscription.trial_days', 14)),
            ]);
        }

        $modulSemua = ModulePrice::aktif()->get();

        foreach ($this->getLembagas() as $lembaga) {
            foreach ($modulSemua as $mp) {
                $existing = $lembaga->modules()->where('module_price_id', $mp->id)->first();

                if ($existing) {
                    $existing->update(['is_active' => true, 'aktif_sejak' => now(), 'nonaktif_sejak' => null]);
                } else {
                    $lembaga->modules()->create([
                        'module_price_id' => $mp->id,
                        'is_active' => true,
                        'aktif_sejak' => now(),
                    ]);
                }
            }
        }

        Notification::make()
            ->title('Paket Full diaktifkan')
            ->body('Semua modul sekarang termasuk di seluruh Lembaga Anda.')
            ->success()
            ->send();
    }

    public function isPaketFullAktif(): bool
    {
        return (bool) $this->getSubscriptionAktif()?->plan?->termasuk_semua_modul;
    }

    /**
     * Kebalikan dari aktifkanPaketFull() -- pindah balik ke plan
     * 'akses-platform', modul yang sudah aktif TETAP aktif (tidak
     * dimatikan otomatis), tapi sekarang dihitung satu-satu lagi
     * (bukan flat Paket Full). Tenant tinggal uncheck manual kalau
     * mau kurangi.
     */
    public function batalkanPaketFull(): void
    {
        $yayasan = $this->getYayasan();
        $planDasar = \App\Models\SubscriptionPlan::where('slug', 'akses-platform')->firstOrFail();

        $subAktif = $yayasan->activeSubscription();

        if ($subAktif) {
            $subAktif->update(['subscription_plan_id' => $planDasar->id]);
        }

        Notification::make()
            ->title('Paket Full dibatalkan')
            ->body('Sekarang dihitung per modul yang dicentang.')
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
