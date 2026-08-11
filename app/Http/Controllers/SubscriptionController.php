<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Halaman "Langganan Saya" — status trial/aktif, daftar paket,
     * dan riwayat pembayaran.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $yayasan = $user->yayasan;

        abort_if(! $yayasan, 404);

        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('urutan')
            ->get();

        $subscriptions = $yayasan->subscriptions()
            ->with(['plan', 'payments'])
            ->latest()
            ->get();

        return view('public.langganan', [
            'yayasan' => $yayasan,
            'plans' => $plans,
            'subscriptions' => $subscriptions,
            'xenditEnabled' => filled(config('services.xendit.secret_key')),
            'bank' => config('subscription.manual_transfer'),
        ]);
    }

    /**
     * Ajukan pembayaran lewat transfer manual — bikin Subscription +
     * SubscriptionPayment berstatus 'pending', nunggu diverifikasi
     * admin platform lewat panel (menu Langganan Yayasan).
     */
    public function payManual(Request $request, SubscriptionPlan $plan)
    {
        $request->validate([
            'bukti_transfer' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $yayasan = $request->user()->yayasan;

        abort_if(! $yayasan, 404);

        $path = $request->file('bukti_transfer')->store('bukti-transfer', 'r2-private');

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $subscription->payments()->create([
            'jumlah' => $plan->harga_bulanan,
            'metode' => 'manual_transfer',
            'status' => 'pending',
            'bukti_transfer' => $path,
        ]);

        return back()->with('success', 'Bukti transfer berhasil dikirim. Langganan akan aktif setelah diverifikasi admin (biasanya 1x24 jam kerja).');
    }

    /**
     * Bayar lewat Xendit — SATU-SATUNYA jalur pembayaran otomatis
     * yang dipakai (revisi keputusan: hanya Xendit, tidak lagi
     * Duitku/Midtrans untuk billing langganan). payDuitku() &
     * payMidtrans() di bawah dibiarkan ada (tidak dihapus, supaya
     * tidak kehilangan kode kalau suatu saat dibutuhkan lagi) tapi
     * TIDAK dipanggil dari halaman "Langganan Saya" lagi.
     */
    public function payXendit(Request $request, SubscriptionPlan $plan)
    {
        if (blank(config('services.xendit.secret_key'))) {
            return back()->with('error', 'Pembayaran otomatis belum diaktifkan. Silakan pakai transfer manual dulu.');
        }

        $user = $request->user();
        $yayasan = $user->yayasan;

        abort_if(! $yayasan, 404);

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $xendit = app(\App\Services\XenditSubscriptionService::class);

        try {
            $invoiceUrl = $xendit->createTransaction($subscription, $plan, $yayasan->email ?? $user->email);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat transaksi pembayaran: ' . $e->getMessage());
        }

        return redirect($invoiceUrl);
    }

    /**
     * Bayar lewat Duitku — DIPERTAHANKAN sebagai kode cadangan, TIDAK
     * lagi dipakai di halaman "Langganan Saya" sejak keputusan
     * revisi payment gateway ke Xendit-only.
     */
    public function payDuitku(Request $request, SubscriptionPlan $plan)
    {
        if (blank(config('services.duitku.merchant_code'))) {
            return back()->with('error', 'Pembayaran otomatis belum diaktifkan. Silakan pakai transfer manual dulu.');
        }

        $user = $request->user();
        $yayasan = $user->yayasan;

        abort_if(! $yayasan, 404);

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $duitku = app(\App\Services\DuitkuSubscriptionService::class);

        try {
            $paymentUrl = $duitku->createTransaction($subscription, $plan, $yayasan->email ?? $user->email);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat transaksi pembayaran: ' . $e->getMessage());
        }

        return redirect($paymentUrl);
    }

    /**
     * Bayar lewat Midtrans (Snap).
     *
     * BELUM AKTIF — butuh MIDTRANS_SERVER_KEY & MIDTRANS_CLIENT_KEY
     * diisi di .env dulu (lihat config/subscription.php). Struktur
     * kodenya sudah disiapkan (lihat App\Services\MidtransService),
     * tinggal diisi kredensial sandbox/production Midtrans kalian.
     * Duitku (payDuitku di atas) adalah jalur otomatis UTAMA karena
     * sudah ada kredensialnya di aplikasi ini — pakai method ini
     * hanya kalau memang mau tambah Midtrans sebagai pilihan kedua.
     */
    public function payMidtrans(Request $request, SubscriptionPlan $plan)
    {
        if (blank(config('subscription.midtrans.server_key'))) {
            return back()->with('error', 'Pembayaran otomatis belum diaktifkan. Silakan pakai transfer manual dulu.');
        }

        $yayasan = $request->user()->yayasan;

        abort_if(! $yayasan, 404);

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $midtrans = app(\App\Services\MidtransService::class);

        $snapUrl = $midtrans->createTransaction($subscription, $plan);

        return redirect($snapUrl);
    }
}
