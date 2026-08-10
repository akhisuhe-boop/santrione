<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Integrasi Duitku untuk pembayaran langganan (subscription billing).
 *
 * Mengikuti PERSIS pola yang sudah terbukti jalan di aplikasi ini
 * (lihat WaliDashboardController::duitku() untuk pembayaran tagihan
 * SPP) — config, endpoint, dan rumus signature yang sama, supaya
 * kredensial Duitku yang sudah ada di .env langsung bisa dipakai
 * tanpa setup akun baru.
 */
class DuitkuSubscriptionService
{
    protected function merchantCode(): string
    {
        $code = config('services.duitku.merchant_code');

        if (blank($code)) {
            throw new RuntimeException('DUITKU_MERCHANT_CODE belum diisi di .env');
        }

        return $code;
    }

    protected function apiKey(): string
    {
        $key = config('services.duitku.api_key');

        if (blank($key)) {
            throw new RuntimeException('DUITKU_API_KEY belum diisi di .env');
        }

        return $key;
    }

    protected function endpoint(): string
    {
        return config('services.duitku.sandbox')
            ? 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry'
            : 'https://passport.duitku.com/webapi/api/merchant/v2/inquiry';
    }

    /**
     * Bikin transaksi Duitku, kembalikan URL pembayarannya (Duitku
     * Payment Page — nampilin semua metode: VA, QRIS, e-wallet, dst,
     * customer pilih sendiri di sana, beda dari alur tagihan SPP yang
     * minta pilih metode dulu di form kita).
     */
    public function createTransaction(
        Subscription $subscription,
        SubscriptionPlan $plan,
        string $email
    ): string {

        $merchantCode = $this->merchantCode();
        $apiKey = $this->apiKey();

        // totalTagihan() pakai computed_amount kalau sudah dihitung
        // TenantBillingCalculator (skema à la carte baru), fallback ke
        // plan->harga_bulanan untuk subscription lama/plan flat biasa
        // — subscription lama TIDAK terpengaruh perubahan ini.
        $amount = $subscription->totalTagihan() ?: (int) $plan->harga_bulanan;
        $merchantOrderId = 'SUB-' . $subscription->id . '-' . time();

        $signature = md5($merchantCode . $merchantOrderId . $amount . $apiKey);

        $response = Http::post($this->endpoint(), [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $amount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => 'Langganan ' . $plan->nama . ' (1 bulan) — ' . $subscription->yayasan->nama,
            'email' => $email,
            'customerVaName' => $subscription->yayasan->nama,
            'callbackUrl' => route('duitku.callback'),
            'returnUrl' => route('subscription.show'),
            'signature' => $signature,
        ]);

        $result = $response->json();

        if (! isset($result['paymentUrl'])) {
            throw new RuntimeException($result['Message'] ?? 'Gagal membuat transaksi Duitku');
        }

        $subscription->payments()->create([
            'jumlah' => $amount,
            'metode' => 'duitku',
            'status' => 'pending',
            'gateway_order_id' => $merchantOrderId,
            'gateway_raw_response' => $result,
        ]);

        return $result['paymentUrl'];
    }
}
