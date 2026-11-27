<?php

return [
    'enabled' => (bool) env('PAYMENT_ENABLED', false),
    'mode' => env('PAYMENT_MODE', 'development'),
    'provider' => env('PAYMENT_PROVIDER'),
    'currency' => env('PAYMENT_CURRENCY', 'IDR'),
    'callback_url' => env('PAYMENT_CALLBACK_URL'),
    'webhook_url' => env('PAYMENT_WEBHOOK_URL'),
    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
    'api_key' => env('PAYMENT_API_KEY'),
    'api_secret' => env('PAYMENT_API_SECRET'),
    'timeout' => (int) env('PAYMENT_TIMEOUT', 30),
    'retry_enabled' => (bool) env('PAYMENT_RETRY_ENABLED', false),
    'mock' => [
        'enabled' => (bool) env('PAYMENT_MOCK_ENABLED', false),
    ],
    'utility_bills' => [
        'enabled' => (bool) env('UTILITY_BILL_PAYMENTS_ENABLED', false),
        'provider' => env('UTILITY_BILL_PROVIDER'),
    ],
];
