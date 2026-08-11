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
 */
class Langganan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Langganan';
    protected static ?string $title = 'Langganan Saya';
    protected static ?int $navigationSort = -20;
    protected static ?string $slug = 'langganan';

    protected static string $view = 'filament.pages.langganan';

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

    public function getEstimasi(): array
    {
        return app(TenantBillingCalculator::class)->hitungYayasan($this->getYayasan());
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

    public function bayarSekarang(): void
    {
        $yayasan = $this->getYayasan();
        $plan = \App\Models\SubscriptionPlan::where('slug', 'akses-platform')->firstOrFail();

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
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
     */
    public function aktifkanPaketFull(): void
    {
        $yayasan = $this->getYayasan();
        $planFull = \App\Models\SubscriptionPlan::where('slug', 'paket-full')->first();

        if (! $planFull) {
            Notification::make()->title('Paket Full belum tersedia')->danger()->send();

            return;
        }

        $subAktif = $yayasan->activeSubscription();

        if ($subAktif) {
            $subAktif->update(['subscription_plan_id' => $planFull->id]);
        } else {
            $yayasan->subscriptions()->create([
                'subscription_plan_id' => $planFull->id,
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

    public function getBroadcasts()
    {
        return PlatformBroadcast::where('status', '!=', 'draft')
            ->latest('dikirim_pada')
            ->get()
            ->filter(fn ($b) => $b->includesYayasan($this->getYayasan()))
            ->take(10);
    }
}
