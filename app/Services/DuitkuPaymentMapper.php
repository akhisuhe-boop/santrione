<?php

namespace App\Services;

class DuitkuPaymentMapper
{
    public static function normalize($paymentMethods)
    {
        return collect($paymentMethods)->map(function ($item) {

            $raw = strtoupper($item['code'] ?? '');

            return [
                'raw' => $raw,
                'name' => $item['name'] ?? '-',
                'category' => $item['category'] ?? 'OTHER',

                // AUTO DETECT CHANNEL
                'channel' => self::detectChannel($raw),
            ];
        });
    }

    private static function detectChannel($code)
    {
        return match (true) {

            str_contains($code, 'BCA') => 'BCA',
            str_contains($code, 'BNI') => 'BNI',
            str_contains($code, 'BRI') => 'BRI',
            str_contains($code, 'MANDIRI') => 'MANDIRI',
            str_contains($code, 'BSI') => 'BSI',

            str_contains($code, 'OVO') => 'OV',
            str_contains($code, 'DANA') => 'DA',
            str_contains($code, 'SHOPEE') => 'SP',

            str_contains($code, 'QRIS') => 'QRIS',

            str_contains($code, 'ALFAMART') => 'ALFAMART',
            str_contains($code, 'INDOMARET') => 'INDOMARET',

            default => 'DEFAULT'
        };
    }
}