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
    /**
     * DIRETIRE — halaman ini dulu Blade route publik terpisah, sekarang
     * digantikan Filament Page App\Filament\Pages\Langganan (di dalam
     * panel, ada di sidebar). Method ini SEKARANG cuma redirect,
     * supaya link lama/bookmark lama tidak 404 begitu saja.
     */
    public function show(Request $request)
    {
        $yayasan = $request->user()->yayasan;

        abort_if(! $yayasan, 404);

        return redirect()->to('/admin/' . $yayasan->slug . '/langganan');
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
     * Bayar lewat Xendit -- dua jalur otomatis yang dipakai sekarang
     * adalah Xendit (method ini) dan DOKU (payDoku() di bawah). Duitku
     * & Midtrans sudah dihapus total dari aplikasi (lihat riwayat git)
     * sesuai keputusan payment gateway final: Xendit + DOKU saja.
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
     * Bayar lewat DOKU -- pilihan kedua di samping Xendit (bukan split
     * payment sub-account seperti pembayaran wali murid, karena ini
     * billing Qinara -> Yayasan langsung, bukan dana yang perlu
     * dirutekan ke sub-account manapun).
     */
    public function payDoku(Request $request, SubscriptionPlan $plan)
    {
        if (blank(config('services.doku.client_id'))) {
            return back()->with('error', 'Pembayaran otomatis belum diaktifkan. Silakan pakai transfer manual dulu.');
        }

        $user = $request->user();
        $yayasan = $user->yayasan;

        abort_if(! $yayasan, 404);

        $subscription = $yayasan->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'status' => 'pending',
        ]);

        $amount = $subscription->totalTagihan() ?: (int) $plan->harga_bulanan;
        $referenceId = 'SUB-' . $subscription->id . '-' . time();

        $doku = app(\App\Services\DokuService::class);

        try {
            $result = $doku->buatPaymentRequest(
                referenceId: $referenceId,
                amount: $amount,
                customerName: $yayasan->nama,
                customerEmail: $yayasan->email ?? $user->email,
                judul: 'Langganan ' . $plan->nama . ' (1 bulan) -- ' . $yayasan->nama,
                channel: 'QRIS'
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat transaksi pembayaran: ' . $e->getMessage());
        }

        $paymentUrl = $result['response']['url'] ?? $result['payment']['url'] ?? $result['url'] ?? null;

        if (! $paymentUrl) {
            return back()->with('error', 'Gagal membuat transaksi pembayaran (URL tidak ditemukan di respons DOKU)');
        }

        $subscription->payments()->create([
            'jumlah' => $amount,
            'metode' => 'doku',
            'status' => 'pending',
            'gateway_order_id' => $referenceId,
            'gateway_raw_response' => $result,
        ]);

        return redirect($paymentUrl);
    }
}
