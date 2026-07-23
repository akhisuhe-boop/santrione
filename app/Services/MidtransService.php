<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Integrasi Midtrans Snap.
 *
 * STATUS: scaffold/kerangka — struktur & alurnya sudah benar mengikuti
 * dokumentasi resmi Midtrans Snap API, TAPI belum pernah dites ke
 * sandbox Midtrans yang sesungguhnya (butuh MIDTRANS_SERVER_KEY di
 * .env yang tidak saya punya aksesnya dari sini). Setelah isi
 * kredensial di .env, coba dulu 1 transaksi lewat sandbox Midtrans
 * sebelum dipakai production.
 *
 * Dokumentasi: https://docs.midtrans.com/reference/snap-quickstart
 */
class MidtransService
{
    protected function serverKey(): string
    {
        $key = config('subscription.midtrans.server_key');

        if (blank($key)) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diisi di .env');
        }

        return $key;
    }

    protected function baseUrl(): string
    {
        return config('subscription.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v1'
            : 'https://app.sandbox.midtrans.com/snap/v1';
    }

    /**
     * Bikin transaksi Snap, kembalikan URL redirect pembayarannya.
     */
    public function createTransaction(Subscription $subscription, SubscriptionPlan $plan): string
    {
        $orderId = 'SUB-' . $subscription->id . '-' . now()->timestamp;

        $response = Http::withBasicAuth($this->serverKey(), '')
            ->acceptJson()
            ->post($this->baseUrl() . '/transactions', [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $plan->harga_bulanan,
                ],
                'customer_details' => [
                    'first_name' => $subscription->yayasan->nama,
                    'email' => $subscription->yayasan->email,
                ],
                'item_details' => [[
                    'id' => 'plan-' . $plan->id,
                    'price' => (int) $plan->harga_bulanan,
                    'quantity' => 1,
                    'name' => 'Langganan ' . $plan->nama . ' (1 bulan)',
                ]],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal membuat transaksi Midtrans: ' . $response->body());
        }

        $subscription->payments()->create([
            'jumlah' => $plan->harga_bulanan,
            'metode' => 'midtrans',
            'status' => 'pending',
            'gateway_order_id' => $orderId,
            'gateway_raw_response' => $response->json(),
        ]);

        return $response->json('redirect_url');
    }

    /**
     * Verifikasi signature notifikasi webhook Midtrans, supaya request
     * yang masuk ke /webhooks/midtrans benar-benar dari Midtrans
     * (bukan orang iseng yang tahu order_id-nya).
     */
    public function verifySignature(array $payload): bool
    {
        $expected = hash('sha512',
            $payload['order_id']
            . $payload['status_code']
            . $payload['gross_amount']
            . $this->serverKey()
        );

        return hash_equals($expected, $payload['signature_key'] ?? '');
    }
}
