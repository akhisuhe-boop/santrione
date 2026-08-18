<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Integrasi Xendit untuk billing langganan (Yayasan -> Qinara).
 *
 * BEDA dari XenditService (yang dipakai untuk split payment SPP wali
 * murid via xenPlatform): ini pembayaran BIASA, langsung ke akun
 * utama Qinara, TIDAK perlu sub-account/split -- jadi TIDAK perlu
 * menunggu review xenPlatform selesai, bisa langsung dipakai begitu
 * XENDIT_SECRET_KEY terisi.
 *
 * Pakai Xendit Invoice API (bukan Payment Request langsung) karena
 * hasilnya berupa 1 URL hosted checkout (pelanggan pilih sendiri
 * QRIS/VA/e-wallet di halaman itu) -- polanya sama seperti Duitku
 * Payment Page yang sudah dipakai sebelumnya, jadi UX-nya konsisten,
 * tidak perlu bangun UI render QR sendiri.
 *
 * amount SELALU dari $subscription->totalTagihan() (computed_amount
 * yang sudah dihitung TenantBillingCalculator sebelum baris Subscription
 * ini dibuat -- lihat GenerateTenantInvoices / GenerateAnnualInvoices /
 * Langganan::bayarSekarang()) -- service ini TIDAK menghitung ulang
 * apa pun, cuma membungkus angka yang sudah final jadi invoice Xendit.
 */
class XenditSubscriptionService
{
    protected function secretKey(): string
    {
        $key = config('services.xendit.secret_key');

        if (blank($key)) {
            throw new RuntimeException('XENDIT_SECRET_KEY belum diisi di .env');
        }

        return $key;
    }

    protected function authHeader(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->secretKey() . ':'),
        ];
    }

    public function createTransaction(
        Subscription $subscription,
        SubscriptionPlan $plan,
        string $email
    ): string {

        $amount = $subscription->totalTagihan() ?: (int) $plan->harga_bulanan;
        $externalId = 'SUB-' . $subscription->id . '-' . time();
        $labelSiklus = $subscription->isTahunan() ? '1 tahun' : '1 bulan';

        $response = Http::withHeaders($this->authHeader())
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $externalId,
                'amount' => $amount,
                'payer_email' => $email,
                'description' => 'Langganan ' . $plan->nama . ' (' . $labelSiklus . ') — ' . $subscription->yayasan->nama,
                'currency' => 'IDR',
                'success_redirect_url' => route('subscription.show'),
                'failure_redirect_url' => route('subscription.show'),
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json('message') ?? 'Gagal membuat invoice Xendit: ' . $response->body()
            );
        }

        $result = $response->json();

        if (! isset($result['invoice_url'])) {
            throw new RuntimeException('Respons Xendit tidak mengandung invoice_url: ' . $response->body());
        }

        $subscription->payments()->create([
            'jumlah' => $amount,
            'metode' => 'xendit',
            'status' => 'pending',
            'gateway_order_id' => $externalId,
            'gateway_raw_response' => $result,
        ]);

        return $result['invoice_url'];
    }
}
