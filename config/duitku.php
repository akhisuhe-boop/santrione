<?php

return [
    'merchant_code' => env('DUITKU_MERCHANT_CODE'),
    'api_key'       => env('DUITKU_API_KEY'),
    'sandbox'       => env('DUITKU_SANDBOX', true),

    'base_url' => env(
        'DUITKU_BASE_URL',
        'https://sandbox.duitku.com/webapi/api/merchant/v2'
    ),

    'callback_url' => env('DUITKU_CALLBACK_URL'),
    'return_url'   => env('DUITKU_RETURN_URL'),
];