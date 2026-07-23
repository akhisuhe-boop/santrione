<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPayment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Endpoint yang dipanggil server Midtrans setelah status
     * pembayaran berubah (settlement, expire, cancel, dll).
     *
     * PENTING: rute ini dikecualikan dari CSRF (lihat bootstrap/app.php
     * atau VerifyCsrfToken $except) karena yang memanggil server
     * Midtrans, bukan browser dengan session Laravel.
     */
    public function handle(Request $request, MidtransService $midtrans)
    {
        $payload = $request->all();

        if (! $midtrans->verifySignature($payload)) {
            Log::warning('Midtrans webhook: signature tidak valid', $payload);
            return response()->json(['message' => 'invalid signature'], 403);
        }

        $payment = SubscriptionPayment::where('gateway_order_id', $payload['order_id'] ?? null)->first();

        if (! $payment) {
            Log::warning('Midtrans webhook: order_id tidak ditemukan', $payload);
            return response()->json(['message' => 'order not found'], 404);
        }

        $status = $payload['transaction_status'] ?? null;

        $payment->update([
            'gateway_transaction_id' => $payload['transaction_id'] ?? null,
            'gateway_raw_response' => $payload,
        ]);

        if (in_array($status, ['settlement', 'capture'], true)) {

            $payment->update(['status' => 'berhasil']);

            $subscription = $payment->subscription;

            $subscription->update([
                'status' => 'active',
                'mulai_pada' => now(),
                'berakhir_pada' => now()->addMonth(),
            ]);

            $subscription->yayasan->update(['status' => 'active']);

        } elseif (in_array($status, ['expire', 'cancel', 'deny'], true)) {

            $payment->update(['status' => 'gagal']);
        }

        return response()->json(['message' => 'ok']);
    }
}
