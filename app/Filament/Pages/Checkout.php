<?php

namespace App\Filament\Pages;

use App\Models\ModulePrice;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\TenantBillingCalculator;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

/**
 * DITAMBAHKAN -- halaman "Checkout" TERPISAH dari "Langganan".
 *
 * SEBELUMNYA semua ini (toggle siklus, pilih modul, Paket Full, bayar)
 * ada langsung di halaman Langganan. Dipisah sesuai permintaan user:
 * Langganan sekarang MURNI informasi (lihat Langganan.php), sedangkan
 * SEMUA aksi checkout (pilih siklus/modul/Paket Full, pilih metode
 * bayar, submit) pindah ke sini.
 *
 * TIDAK ada di navigasi (shouldRegisterNavigation = false) -- dicapai
 * lewat tombol "Bayar / Kelola Langganan" di halaman Langganan, atau
 * langsung link (mis. banner tagihan pending).
 *
 * DITAMBAHKAN JUGA -- metode pembayaran Transfer Manual (sebelumnya
 * cuma ada DOKU). Rute lama SubscriptionController::payManual() tetap
 * ada tapi TIDAK dipakai lagi setelah ini (form-nya sekarang langsung
 * di halaman Livewire ini, bukan POST ke controller terpisah), supaya
 * konsisten satu alur dengan DOKU (sama-sama lewat halaman ini).
 */
class Checkout extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $title = 'Checkout Langganan';
    protected static ?string $slug = 'checkout-langganan';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.checkout';

    public string $billingCycle = 'bulanan';

    public string $metodePembayaran = 'doku';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $buktiTransfer = null;

    // Sama seperti dulu di Langganan.php -- lihat catatan di sana soal
    // kenapa ini perlu (cegah transaksi DOKU dobel kalau diklik ulang).
    public ?string $pendingUrl = null;

    protected ?\Illuminate\Support\Collection $lembagasCache = null;

    protected ?\Illuminate\Support\Collection $modulOptionsCache = null;

    public function mount(): void
    {
        $this->billingCycle = $this->getSubscriptionAktif()?->siklus_billing ?? 'bulanan';
        $this->pendingUrl = $this->cariPendingUrlPembayaran();
    }

    public static function canAccess(): bool
    {
        // Sama persis dengan Langganan::canAccess() -- lihat catatan di
        // sana. Checkout harus SELALU bisa dicapai dalam kondisi darurat
        // yang sama (Yayasan tidak punya akses), karena ini satu-satunya
        // tempat tenant BENERAN bisa menyelesaikan pembayaran.
        if (auth()->user()?->is_platform_admin) {
            return false;
        }

        $user = auth()->user();

        if (! $user?->yayasan_id) {
            return false;
        }

        $yayasan = $user->yayasan;

        if ($yayasan && ! $yayasan->hasAccess()) {
            return true;
        }

        return (bool) $user->can('page_Langganan');
    }

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

    /**
     * DITAMBAHKAN -- true kalau sudah ada transfer manual yang dikirim
     * untuk plan & siklus ini dan MASIH menunggu verifikasi admin.
     * Beda dari cariPendingUrlPembayaran() (itu khusus DOKU, ada URL
     * checkout untuk dilanjutkan) -- transfer manual tidak punya URL,
     * cuma perlu tunggu, jadi tenant tidak boleh kirim dobel.
     */
    protected function adaPendingManualMenunggu(int $planId, string $siklus): bool
    {
        return $this->getYayasan()->subscriptions()
            ->where('status', 'pending')
            ->where('subscription_plan_id', $planId)
            ->where('siklus_billing', $siklus)
            ->whereHas('payments', fn ($q) => $q->where('metode', 'manual_transfer')->where('status', 'pending'))
            ->exists();
    }

    public function getYayasan()
    {
        return Auth::user()->yayasan;
    }

    public function shouldShowBayarButton(): bool
    {
        $yayasan = $this->getYayasan();

        if ($yayasan->status !== 'active') {
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

    public function setMetodePembayaran(string $metode): void
    {
        $this->metodePembayaran = in_array($metode, ['doku', 'manual'], true) ? $metode : 'doku';
    }

    public function isTahunanDipilih(): bool
    {
        return $this->billingCycle === 'tahunan';
    }

    public function isPreviewBerbedaDariAktif(): bool
    {
        $aktif = $this->getSubscriptionAktif();

        return $aktif && $aktif->status === 'active' && $aktif->siklus_billing !== $this->billingCycle;
    }

    public function getEstimasi(): array
    {
        $calculator = app(TenantBillingCalculator::class);
        $yayasan = $this->getYayasan();

        return $this->isTahunanDipilih()
            ? $calculator->hitungYayasanTahunan($yayasan)
            : $calculator->hitungYayasan($yayasan);
    }

    public function getEstimasiPaketFull(): ?array
    {
        $planFull = SubscriptionPlan::where('slug', 'paket-full')->first();

        if (! $planFull) {
            return null;
        }

        $calculator = app(TenantBillingCalculator::class);
        $yayasan = $this->getYayasan();

        return $this->isTahunanDipilih()
            ? $calculator->hitungYayasanTahunan($yayasan, $planFull)
            : $calculator->hitungYayasan($yayasan, $planFull);
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

    /**
     * DITAMBAHKAN -- validasi file transfer manual SEBELUM Subscription
     * dibuat sama sekali (bukan sesudahnya), supaya tidak ada baris
     * Subscription 'pending' nyangkut tanpa Payment kalau validasi
     * gagal.
     */
    private function validasiBuktiTransfer(): bool
    {
        try {
            $this->validate([
                'buktiTransfer' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            ], [], [
                'buktiTransfer' => 'Bukti Transfer',
            ]);

            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            Notification::make()
                ->title('Upload bukti transfer dulu')
                ->body('File wajib diisi, format JPG/PNG/PDF, maksimal 5MB.')
                ->danger()
                ->send();

            return false;
        }
    }

    public function bayarSekarang(): void
    {
        $yayasan = $this->getYayasan();
        $plan = SubscriptionPlan::where('slug', 'akses-platform')->firstOrFail();
        $tahunan = $this->isTahunanDipilih();
        $siklus = $tahunan ? 'tahunan' : 'bulanan';

        if ($this->metodePembayaran === 'manual' && ! $this->validasiBuktiTransfer()) {
            return;
        }

        $pendingUrlCocok = $this->cariPendingUrlPembayaran($plan->id, $siklus);

        if ($pendingUrlCocok) {
            $this->redirect($pendingUrlCocok);

            return;
        }

        if ($this->metodePembayaran === 'manual' && $this->adaPendingManualMenunggu($plan->id, $siklus)) {
            Notification::make()
                ->title('Sudah ada transfer yang menunggu verifikasi')
                ->body('Anda sudah mengirim bukti transfer untuk ini sebelumnya. Mohon tunggu admin Qinara memverifikasi (biasanya 1x24 jam kerja).')
                ->warning()
                ->send();

            return;
        }

        $calculator = app(TenantBillingCalculator::class);

        $hasil = $tahunan
            ? $calculator->hitungYayasanTahunan($yayasan)
            : $calculator->hitungYayasan($yayasan);

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'siklus_billing' => $siklus,
            'status' => 'pending',
            'computed_amount' => $hasil['total'],
            'computed_breakdown' => $hasil,
            'periode' => $tahunan ? (string) now()->addYear()->year : now()->format('Y-m'),
        ]);

        $this->tandaiPromoTerpakai($yayasan, $hasil, 'bayarSekarang');

        if ($this->metodePembayaran === 'manual') {
            $this->prosesManualDanNotify($subscription);

            return;
        }

        $this->buatTransaksiDokuDanRedirect($yayasan, $subscription, $plan, $hasil['total'], $tahunan);
    }

    protected function buatTransaksiDokuDanRedirect(
        \App\Models\Yayasan $yayasan,
        Subscription $subscription,
        SubscriptionPlan $plan,
        int $amount,
        bool $tahunan
    ): void {
        if (blank(config('services.doku.client_id'))) {
            Notification::make()
                ->title('Pembayaran otomatis belum diaktifkan')
                ->body('Silakan pilih metode Transfer Manual di atas.')
                ->danger()
                ->send();

            return;
        }

        $referenceId = 'SUB-'.$subscription->id.'-'.time();

        $doku = app(\App\Services\DokuService::class);

        try {
            $result = $doku->buatPaymentRequest(
                referenceId: $referenceId,
                amount: $amount,
                customerName: $yayasan->nama,
                customerEmail: $yayasan->email ?? Auth::user()->email,
                judul: 'Langganan '.$plan->nama.' ('.($tahunan ? '1 tahun' : '1 bulan').') -- '.$yayasan->nama,
                channel: 'ALL',
                callbackUrl: rtrim(config('app.url'), '/').'/admin/'.$yayasan->slug
            );
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal membuat transaksi pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

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
     * DITAMBAHKAN -- padanan buatTransaksiDokuDanRedirect() tapi untuk
     * transfer manual: simpan file bukti ke disk PRIVATE (bukan
     * public -- ini bukti transfer bank, sensitif), buat Payment
     * berstatus 'pending', notify tenant. Verifikasi selanjutnya lewat
     * panel Platform (Verifikasi Pembayaran) seperti biasa -- begitu
     * admin klik "Verifikasi & Aktifkan", SubscriptionPaymentObserver
     * yang otomatis mencatatnya sebagai Kas Masuk di pembukuan Qinara.
     */
    protected function prosesManualDanNotify(Subscription $subscription): void
    {
        try {
            $path = $this->buktiTransfer->store('bukti-transfer', 'r2-private');
        } catch (\Throwable $e) {
            Log::error('Checkout::prosesManualDanNotify: gagal upload bukti transfer', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Gagal upload bukti transfer')
                ->body('Silakan coba lagi, atau hubungi admin Qinara kalau terus gagal.')
                ->danger()
                ->send();

            return;
        }

        $subscription->payments()->create([
            'jumlah' => $subscription->computed_amount,
            'metode' => 'manual_transfer',
            'status' => 'pending',
            'bukti_transfer' => $path,
        ]);

        $this->buktiTransfer = null;

        Notification::make()
            ->title('Bukti transfer terkirim')
            ->body('Langganan akan aktif setelah diverifikasi admin Qinara (biasanya 1x24 jam kerja).')
            ->success()
            ->send();
    }

    protected function tandaiPromoTerpakai(\App\Models\Yayasan $yayasan, array $hasil, string $asal): void
    {
        if (($hasil['promo_pendaftaran_persen'] ?? 0) <= 0) {
            return;
        }

        $yayasan->update(['promo_pendaftaran_terpakai' => true]);

        try {
            \App\Services\NotificationService::sendBroadcastYayasan(
                $yayasan,
                'Diskon Pendaftaran Diterapkan',
                "Tagihan ini sudah termasuk diskon pendaftaran \"{$hasil['promo_pendaftaran_teks']}\" ({$hasil['promo_pendaftaran_persen']}%). ".
                'Diskon ini berlaku SATU KALI untuk tagihan ini saja -- tagihan berikutnya akan kembali ke harga normal.'
            );
        } catch (\Throwable $e) {
            Log::error("Checkout::{$asal}: gagal kirim notif penjelasan promo untuk yayasan {$yayasan->id}: {$e->getMessage()}");
        }
    }

    public function aktifkanPaketFull(): void
    {
        $yayasan = $this->getYayasan();
        $planFull = SubscriptionPlan::where('slug', 'paket-full')->first();
        $tahunan = $this->isTahunanDipilih();
        $siklus = $tahunan ? 'tahunan' : 'bulanan';

        if (! $planFull) {
            Notification::make()->title('Paket Full belum tersedia')->danger()->send();

            return;
        }

        if ($this->metodePembayaran === 'manual' && ! $this->validasiBuktiTransfer()) {
            return;
        }

        $pendingUrlCocok = $this->cariPendingUrlPembayaran($planFull->id, $siklus);

        if ($pendingUrlCocok) {
            $this->redirect($pendingUrlCocok);

            return;
        }

        if ($this->metodePembayaran === 'manual' && $this->adaPendingManualMenunggu($planFull->id, $siklus)) {
            Notification::make()
                ->title('Sudah ada transfer yang menunggu verifikasi')
                ->body('Anda sudah mengirim bukti transfer untuk ini sebelumnya. Mohon tunggu admin Qinara memverifikasi.')
                ->warning()
                ->send();

            return;
        }

        $calculator = app(TenantBillingCalculator::class);

        $hasil = $tahunan
            ? $calculator->hitungYayasanTahunan($yayasan, $planFull)
            : $calculator->hitungYayasan($yayasan, $planFull);

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
            'siklus_billing' => $siklus,
            'status' => 'pending',
            'computed_amount' => $hasil['total'],
            'computed_breakdown' => $hasil,
            'periode' => $tahunan ? (string) now()->addYear()->year : now()->format('Y-m'),
        ]);

        $this->tandaiPromoTerpakai($yayasan, $hasil, 'aktifkanPaketFull');

        if ($this->metodePembayaran === 'manual') {
            $this->prosesManualDanNotify($subscription);

            return;
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

    public function batalkanPaketFull(): void
    {
        $yayasan = $this->getYayasan();
        $planDasar = SubscriptionPlan::where('slug', 'akses-platform')->firstOrFail();

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
}
