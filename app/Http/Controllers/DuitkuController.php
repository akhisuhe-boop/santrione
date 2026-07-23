<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\Kas;

class DuitkuController extends Controller
{
    private function isDemo()
    {
        return config('app.payment_mode') === 'demo'
            || config('services.duitku.mode') === 'demo'
            || env('PAYMENT_MODE') === 'demo';
    }

    public function createTopup($data)
    {
        $merchantCode = config('services.duitku.merchant_code');
        $apiKey       = config('services.duitku.api_key');

        $merchantOrderId = $data['reference'];
        $amount          = $data['amount'];

        $signature = md5($merchantCode . $merchantOrderId . $amount . $apiKey);

        if ($this->isDemo()) {
            return route('duitku.demo.topup', [
                'reference' => $merchantOrderId,
                'amount'    => $amount
            ]);
        }

        $params = [
            'merchantCode'    => $merchantCode,
            'paymentAmount'   => $amount,
            'merchantOrderId' => $merchantOrderId,
            'productDetails'  => 'Top Up Saldo',
            'customerVaName'  => $data['customer'],
            'email'           => $data['email'],
            'callbackUrl'     => route('duitku.callback'),
            'returnUrl'       => url('/wali/topup'),
            'signature'       => $signature,
        ];

        $ch = curl_init('https://passport.duitku.com/webapi/api/merchant/v2/inquiry');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result, true);

        return $response['paymentUrl'] ?? null;
    }

    public function callback(Request $request)
    {
        if ($this->isDemo()) {
            return response('DEMO MODE - IGNORED', 200);
        }

        $merchantCode = config('services.duitku.merchant_code');
        $apiKey       = config('services.duitku.api_key');

        $signature = md5(
            $merchantCode .
            $request->merchantOrderId .
            $request->amount .
            $apiKey
        );

        if ($signature !== $request->signature) {
            return response('INVALID SIGNATURE', 400);
        }

        // Callback pembayaran LANGGANAN (SaaS billing) — order id-nya
        // sengaja diberi prefix "SUB-" saat dibuat di
        // DuitkuSubscriptionService, supaya bisa dibedakan dari
        // callback top-up wallet di bawah tanpa perlu URL callback
        // terpisah (1 callback URL yang sama sudah terdaftar di
        // dashboard Duitku).
        if (str_starts_with((string) $request->merchantOrderId, 'SUB-')) {
            return $this->handleSubscriptionCallback($request);
        }

        DB::beginTransaction();

        try {

            $trx = WalletTransaction::where('reference_id', $request->merchantOrderId)->first();

            if (!$trx) {
                return response('NOT FOUND', 404);
            }

            if ((string) $request->resultCode === '00') {

                $trx->update([
                    'status' => 'success',
                ]);

                // update wallet
                $wallet = $trx->wallet;

                if ($wallet) {
                    $wallet->increment('saldo', $trx->amount);
                }

                \Log::info('AKAN INSERT KAS', [
                    'rekening_id' => $trx->wallet->rekening_id ?? null,
                    'kategori_id' => 1,
                    'lembaga_id'  => $trx->wallet->lembaga_id ?? null,
                    'nominal'     => $trx->amount,
                ]);
                // 🔥 FIX FINAL: INSERT KAS (PASTI MASUK)
                Kas::create([
                    'tipe'        => 'masuk',
                    'nominal'     => $trx->amount,
                    'sumber'      => 'duitku',
                    'tanggal'     => now(),
                    'keterangan'  => 'Topup Wallet - ' . $trx->reference_id,

                    // 🔥 WAJIB isi default (INI YANG BIKIN GAGAL SELAMA INI)
                    'rekening_id' => $trx->wallet->rekening_id ?? 1,
                    'kategori_id' => 1, // <-- isi kategori kas masuk default
                    'lembaga_id'  => $trx->wallet->lembaga_id ?? 1,
                ]);

            } else {
                $trx->update([
                    'status' => 'failed',
                ]);
            }

            DB::commit();
            return response('OK');

        } catch (\Exception $e) {

            \Log::error('DUITKU CALLBACK ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            DB::rollBack();

            return response('ERROR: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Callback khusus untuk pembayaran LANGGANAN (SaaS billing).
     * Signature sudah diverifikasi oleh pemanggil (callback() di atas)
     * sebelum method ini dipanggil.
     */
    protected function handleSubscriptionCallback(Request $request)
    {
        $payment = \App\Models\SubscriptionPayment::where(
            'gateway_order_id',
            $request->merchantOrderId
        )->first();

        if (! $payment) {
            return response('NOT FOUND', 404);
        }

        DB::beginTransaction();

        try {

            $payment->update([
                'gateway_transaction_id' => $request->reference ?? null,
                'gateway_raw_response' => $request->all(),
            ]);

            if ((string) $request->resultCode === '00') {

                $payment->update(['status' => 'berhasil']);

                $subscription = $payment->subscription;

                $subscription->update([
                    'status' => 'active',
                    'mulai_pada' => now(),
                    'berakhir_pada' => now()->addMonth(),
                ]);

                $subscription->yayasan->update(['status' => 'active']);

            } else {
                $payment->update(['status' => 'gagal']);
            }

            DB::commit();
            return response('OK');

        } catch (\Exception $e) {

            \Log::error('DUITKU SUBSCRIPTION CALLBACK ERROR', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            DB::rollBack();

            return response('ERROR: ' . $e->getMessage(), 500);
        }
    }
}