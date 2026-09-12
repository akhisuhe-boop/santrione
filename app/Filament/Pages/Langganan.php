<?php

namespace App\Filament\Pages;

use App\Models\PlatformBroadcast;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

/**
 * Halaman "Langganan" -- DIUBAH jadi MURNI INFORMASI (jenis langganan,
 * masa aktif, modul aktif per Lembaga, riwayat pembayaran). Semua
 * AKSI (pilih siklus/modul, Paket Full, bayar -- baik DOKU maupun
 * Transfer Manual) pindah ke halaman baru Checkout.php, dicapai lewat
 * tombol "Bayar / Kelola Langganan" di sini.
 *
 * PENTING -- slug & route name TIDAK berubah ('langganan') karena
 * RedirectSuspendedYayasan middleware bergantung pada
 * 'filament.admin.pages.langganan' sebagai jalur darurat SATU-SATUNYA
 * yang selalu bisa diakses Yayasan yang sedang tidak punya akses.
 * Checkout::canAccess() dibuat meniru gate darurat yang sama, dan
 * route Checkout ditambahkan ke $ruteBoleh middleware itu juga --
 * supaya tenant yang di-redirect ke sini TETAP bisa lanjut ke Checkout
 * untuk benar-benar membayar.
 */
class Langganan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Langganan';
    protected static ?string $title = 'Langganan Saya';
    protected static ?int $navigationSort = -20;
    protected static ?string $slug = 'langganan';

    protected static string $view = 'filament.pages.langganan';

    public ?string $pendingUrl = null;

    protected ?\Illuminate\Support\Collection $lembagasCache = null;

    public function mount(): void
    {
        $this->pendingUrl = $this->cariPendingUrlPembayaran();
    }

    /**
     * Sama seperti versi lama di Checkout -- dipertahankan di sini
     * juga (duplikasi kecil, sengaja) supaya banner "ada tagihan
     * pending" tetap bisa ditampilkan di halaman info ini TANPA harus
     * memuat seluruh state Checkout.
     */
    protected function cariPendingUrlPembayaran(): ?string
    {
        $subscription = $this->getYayasan()->subscriptions()
            ->where('status', 'pending')
            ->latest()
            ->first();

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
     * DITAMBAHKAN -- true kalau ada transfer manual yang masih
     * menunggu diverifikasi admin (tidak punya URL redirect seperti
     * DOKU, jadi perlu ditampilkan beda -- "menunggu verifikasi",
     * bukan tombol "Bayar Sekarang").
     */
    public function adaTransferManualMenunggu(): bool
    {
        return $this->getYayasan()->subscriptions()
            ->where('status', 'pending')
            ->whereHas('payments', fn ($q) => $q->where('metode', 'manual_transfer')->where('status', 'pending'))
            ->exists();
    }

    public static function canAccess(): bool
    {
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

    public function getLembagas()
    {
        return $this->lembagasCache ??= $this->getYayasan()->lembagas()
            ->with(['modules.modulePrice'])
            ->get();
    }

    public function getSubscriptionAktif()
    {
        return $this->getYayasan()->activeSubscription();
    }

    public function isPaketFullAktif(): bool
    {
        return (bool) $this->getSubscriptionAktif()?->plan?->termasuk_semua_modul;
    }

    public function getRiwayatPembayaran()
    {
        return $this->getYayasan()->subscriptions()
            ->with(['plan', 'payments'])
            ->latest()
            ->get();
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
