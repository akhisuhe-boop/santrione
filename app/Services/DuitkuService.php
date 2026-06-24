<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DuitkuService
{
    public static function createInvoice(array $data)
    {
        $merchantCode = config('duitku.merchant_code');
        $apiKey       = config('duitku.api_key');

        $orderId = $data['order_id'] ?? null;
        $amount  = (int) ($data['amount'] ?? 0);

        if (!$orderId || !$amount) {
            throw new \Exception('order_id dan amount wajib diisi');
        }

        $params = [
            'merchantCode'    => $merchantCode,
            'paymentAmount'   => $amount,
            'merchantOrderId' => $orderId,
            'productDetails'  => $data['product'] ?? 'Payment',
            'customerVaName'  => $data['customer_name'] ?? 'Customer',
            'email'           => $data['email'] ?? null,

            'callbackUrl'     => config('duitku.callback_url'),
            'returnUrl'       => config('duitku.return_url'),

            'signature'       => self::signature($merchantCode, $apiKey, $orderId, $amount),
        ];

        return Http::asForm()
            ->post(config('duitku.base_url') . '/createInvoice', $params)
            ->json();
    }

    public static function signature($merchantCode, $apiKey, $orderId, $amount)
    {
        return md5($merchantCode . $apiKey . $orderId . $amount);
    }
}